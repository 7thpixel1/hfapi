<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PixelModel;
use App\Config\ApiResponse;
use GuzzleHttp\Client;
use DateTime;
use DateTimeZone;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class PaymentController extends BaseController{

    private $_errorMessage, $_api_url, $_gp_ver, $_logger;

    public function __construct(PixelModel $model) {
        $this->model = $model;
        $this->_api_url = $_ENV['GP_URL'];
        $this->_gp_ver = $_ENV['GP_VER'];

//        $this->_logger = new MonologLogger('app_logger');
//        $this->_logger->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', MonologLogger::DEBUG));

        $this->initGateway();
    }

    private function initGateway() {
        $config = new GpApiConfig();
        $config->appId = $_ENV['GP_APP_ID'];
        $config->appKey = $_ENV['GP_API_KEY'];
        $config->channel = Channel::CardNotPresent;
        $config->country = 'CA';
        $config->environment = ($_ENV['GP_ENV'] === 'TEST') ? Environment::TEST : Environment::PRODUCTION;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        $config->methodNotificationUrl = "https://www.example.com/methodNotificationUrl";
        $config->challengeNotificationUrl = "https://www.example.com/challengeNotificationUrl";
        $config->merchantContactUrl = "https://www.example.com/about";

        ServicesContainer::configureService($config, "default");
    }

    public function generateNonce() {
        $dateTime = new DateTime("now", new DateTimeZone("UTC"));
        return $dateTime->format("Y-m-d\TH:i:s.v\Z");
    }

    public function generateSecret($nonce, $appKey) {

        $data = $nonce . $appKey;
        return hash('sha512', $data);
    }

    private function getAccessToken() {
        $appId = $_ENV['GP_APP_ID'];
        $appKey = $_ENV['GP_API_KEY'];

        $nonce = $this->generateNonce();
        $secret = $this->generateSecret($nonce, $appKey);

        $client = new Client();
        $url = $this->_api_url . "accesstoken";

        $body = [
            'app_id' => $appId,
            'nonce' => $nonce,
            'secret' => $secret,
            'grant_type' => 'client_credentials',
        ];

        try {

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-GP-Version' => $this->_gp_ver,
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {
                $this->_errorMessage = $e->getResponse()->getBody()->getContents();
            } else {
                $this->_errorMessage = $e->getMessage();
            }
            return null;
        }
    }

    private function saveCardRoutine($data) {

        $bearerToken = $this->model->getActiveApiToken();

        if ($bearerToken === null) {
            $newTokenResponse = $this->getAccessToken();
            if ($newTokenResponse) {
                $token = $newTokenResponse['token'];
                $secondsToExpire = (int) $newTokenResponse['seconds_to_expire'] - 600;
                $expiresAt = date("Y-m-d H:i:s", time() + $secondsToExpire);
                $this->model->insertApiToken($token, $expiresAt);
                $bearerToken = $token;
            } else {
                return json_encode(ApiResponse::error("Authorization error."));
            }
        }
        $client = new Client();
        $url = $this->_api_url . "payment-methods";
        $body = [
            "reference" => $data['reference'],
            "usage_mode" => "MULTIPLE",
            "card" => $data['card']
        ];

        try {
            $res = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer $bearerToken",
                    'Content-Type' => 'application/json',
                    'X-GP-Version' => $this->_gp_ver,
                ],
                'json' => $body,
            ]);
            $resObj = json_decode($res->getBody(), true);

            return json_encode(ApiResponse::success($resObj));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return json_encode(ApiResponse::error("Card Save Error: " . $e->getMessage()));
        }
    }

    public function saveCard(Request $request, Response $response, $args) {


        $data = json_decode($request->getBody(), true);
        $res = $this->saveCardRoutine($data);
        $resObj = json_decode($res);
        if ($resObj->status === 200) {
            $response->getBody()->write(json_encode(ApiResponse::success($resObj->data)));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::error(["message" => "Transaction error: " . $resObj->message])));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    

    public function processSales(Request $request, Response $response, $args) {

        $donor_id = (int) $request->getAttribute('user_id');

        $data = json_decode($request->getBody(), true);

        if ($donor_id === 0) {
            $donor = $this->model->getDonorByUsername($data['email']);
            if ($donor === null) {
                //Create Donor
                $donor = $this->saveDonor($data);
            }
        } else {
            $donor = $this->model->getDonor($donor_id);
        }
        $donation = new \stdClass();

        $donation->created_date = $donation->receipt_date = date('Y-m-d H:i:s');
        $donation->created_by = 1;
        $donation->donor_id = (int) $donor->id;
        $donation->status = 0;
        $donation->comments = $data['comments'] ?? NULL;

        $donation->children = $data['children'];
        $donation->project_id = 0;
        $donation->amount = $data['amount'];
        $donation->non_eligible_amount = $data['non_eligible_amount'];
        $donation->eligible_amount = $data['eligible_amount'];
        $donation->address1 = $data['address1'];
        $donation->address2 = $data['address2'] ?? NULL;
        $donation->city = $data['city'];
        $donation->state = $data['state'];
        $donation->country = $data['country'];
        $donation->postal_code = $data['postal_code'];
        $donation->email = $data['email'];
        $donation->batch_id = 0; //Fix this
        $donation->deposit_type = 3;
        $donation->home_phone = $data['phone'];
        $donation->sum_of_string = $data['sum_of_string'];
        $donation->receipt_id = $this->model->getReceiptId();

        $donation->dedication_type = $data['dedication_type'];
        $donation->honoree_first_name = $data['honoree_first_name'];
        $donation->honoree_last_name = $data['honoree_last_name'];
        $donation->comments = $data['message'];
        $donation->is_recurring = ((int) $data['donation_type'] > 0) ? 1 : 0;

        //Payment
        $cardObject = \App\Config\Pixel::decryptObject($data['card_object'], $_ENV['ENC_KEY']);

        $card = new CreditCardData();
        $card->number = $cardObject['card_number'];
        $card->expMonth = $cardObject['expiry_date'];
        $card->expYear = $cardObject['expiry_year'];
        $card->cvn = $cardObject['cvn'];
        $card->cardHolderName = $cardObject['card_holder_name'];
        $cardHolderName = $cardObject['card_holder_name'];
        // Create Address instance
        $address = new Address();
        $address->streetAddress1 = $data['address1'];
        $address->streetAddress2 = $data['address2'] ?? NULL;
        $address->city = $data['city'];
        $address->state = $data['state'];
        $address->postalCode = $data['postal_code'];
        $address->country = $data['country'];

        try {
//->withDynamicDescriptor('HUMANITY FIRST CANADA')  //Consult with Corex  
            $responseCharge = $card->charge(round($data['amount'], 2))
                    ->withCurrency('CAD')
                    ->withAllowDuplicates(false)
                    ->withClientTransactionId("HF-" . $donation->receipt_id)
                    ->withAddress($address)
                    ->execute();
            $paymentRtnObject = \App\Config\Pixel::flattenObject($responseCharge);
            $donation->cheque_trans_no = $paymentRtnObject['transactionReference_transactionId'];
            $donation->online_batch_id = $paymentRtnObject['batchSummary_batchReference'];
            $donation->is_online = '1'; //$fObj['']
            $paymentRtnObject['donorId'] = $donation->donor_id;
            $paymentRtnObject['meta_info'] = $data['meta_info'];
            $paymentRtnObject['donationId'] = 0;
            if ($paymentRtnObject['responseCode'] === 'SUCCESS') {

                $donation_id = $this->model->addDonation($donation);
                $paymentRtnObject['donationId'] = $donation_id;

                $responseBody = [
                    'transaction_id' => $responseCharge->transactionId,
                    'batch_id' => $responseCharge->batchSummary->batchReference,
                    'reference_number' => $responseCharge->referenceNumber,
                    'donation_type' => $data['donation_type'],
                    'donation_id' => $donation_id,
                    'donor_id' => $donor->id
                ];
                //time to save card
                if ((int) $data['donation_type'] > 0) {
                    $card = new \stdClass();
                    $card->number = $cardObject['card_number'];
                    $card->expiry_month = $cardObject['expiry_date'];
                    $card->expiry_year = substr($cardObject['expiry_year'], -2);
                    $card->cvv = $cardObject['cvn'];
                    $saveObject = ["reference" => "DNR_" . $donor->id, "card" => $card];
                    $card_res = $this->saveCardRoutine($saveObject);
                    $card_res_json = json_decode($card_res, true);
                    $responseBody['is_card_saved'] = 0;
                    if ((int) $card_res_json['status'] === 200) {
                        $saveCardResponse = $card_res_json['data'];
                        if ($saveCardResponse['status'] === 'ACTIVE') {
                            $responseBody['is_card_saved'] = 1;
                            $saveCardResponse['card_holder_name'] = $cardHolderName;
                            $saveCardResponse['donor_id'] = $donor->id;
                            $saveCardResponse['donation_id'] = $donation_id;
                            $saveCardResponse['non_eligible_amount'] = $data['non_eligible_amount'];
                            $saveCardResponse['eligible_amount'] = $data['eligible_amount'];
                            $saveCardResponse['project_id'] = 0;
                            $saveCardResponse['message'] = $data['message'];
                            $saveCardResponse['frequency'] = (int) $data['donation_type'];
                            $this->model->saveCardData($saveCardResponse);
                        }
                    }
                }
                $response->getBody()->write(json_encode(ApiResponse::success($responseBody)));
            } else {

                $response->getBody()->write(json_encode(ApiResponse::error("We're sorry, but your payment has been declined. "
                                        . "Please check your card details for any errors and try again or use a different card<br>Error Code:(" . $paymentRtnObject['cardIssuerResponse_result'] . ")")));
            }
            //in both cases we have to save the history
            $this->model->saveTransactionHistory($paymentRtnObject);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            
            echo $e->getMessage();
            echo $e->getTraceAsString();
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function donate(Request $request, Response $response, $args) {
        $donor_id = $request->getAttribute('user_id');

        if (TRUE) {
            $response->getBody()->write(json_encode(ApiResponse::success(["message" => "Success"])));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::notFound()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    
}
