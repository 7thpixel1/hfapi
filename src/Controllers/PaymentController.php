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

class PaymentController {

    private $model, $_errorMessage, $_api_url, $_gp_ver;

    public function __construct(PixelModel $model) {
        $this->model = $model;
        $this->_api_url = $_ENV['GP_URL'];
        $this->_gp_ver = $_ENV['GP_VER'];
        $this->initGateway();
    }

    private function initGateway() {
        $config = new GpApiConfig();
        $config->appId = $_ENV['GP_APP_ID'];
        $config->appKey = $_ENV['GP_API_KEY'];
        $config->channel = Channel::CardNotPresent;
        $config->country = 'CA';
        $config->environment = Environment::TEST;
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

    public function saveCard(Request $request, Response $response, $args) {

        $client = new Client();
        $data = json_decode($request->getBody(), true);
        
        $url = $this->_api_url . "payment-methods";
        $bearerToken = $this->model->getActiveApiToken();

        if ($bearerToken === null) {
            $newTokenResponse = $this->getAccessToken();
            if ($newTokenResponse) {
                $token = $newTokenResponse['token'];
                $secondsToExpire = (int) $newTokenResponse['seconds_to_expire'] - 600;
                $expiresAt = date("Y-m-d H:i:s", time() + $secondsToExpire);
                $this->model->insertApiToken($token, $expiresAt);
                $bearerToken = $token;
            } else {//return 
                $response->getBody()->write(json_encode(ApiResponse::error(["message" => "Token Generation error"])));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }

        $body = [
            "reference" => $data['reference'],
            "usage_mode" => "MULTIPLE",
            "card" => $data['card']
        ];

        try {
            // Make the POST request
            $res = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer $bearerToken",
                    'Content-Type' => 'application/json',
                    'X-GP-Version' => $this->_gp_ver,
                ],
                'json' => $body,
            ]);

            // Parse and return the response
            $Obj = json_decode($res->getBody(), true);
            $response->getBody()->write(json_encode(ApiResponse::success($Obj)));
            return $response->withHeader('Content-Type', 'application/json');
            
            
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response->getBody()->write(json_encode(ApiResponse::error(["message" => "Card Save Error: " . $e->getMessage()])));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function processSales(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
//First step is get donor, if user is loggedin, we can send donor_id and donation_id will be zero for first call but for second call it will be > 0

        $donor = $this->model->getDonorByEmailPostalCode($data['email'], $data['postal_code']);
        $donor_id = 0;
        if ($donor === null) {//create donor and get id
            $donor_id = 0;
        } else {
            $donor_id = $donor->id;
        }


        //lets create donation object:
        $donation = new \stdClass();

        $donation->created_date = $donation->receipt_date = date('Y-m-d H:i:s');
        $donation->created_by = 1;
        $donation->donor_id = $donor_id;
        $donation->status = 0;
        $donation->comments = $data['comments'] ?? NULL;

        $donation->children = $data['children'];
        $donation->project_id = 0;
        $donation->amount = $data['amount'];
        $donation->non_eligible_amount = $data['non_eligible_amount'];
        $donation->eligible_amount = $data['eligible_amount'];
        $donation->address1 = $data['address1'];
        $donation->address2 = $data['address2'] ?? NULL;
        $donation->city_id = $data['city'];
        $donation->state_id = $data['province'];
        $donation->country_id = $data['country'];
        $donation->postal_code = $data['postal_code'];
        $donation->email = $data['email'];
        $donation->batch_id = 0; //Fix this
        $donation->deposit_type = 3;
        $donation->home_phone = $data['phone'];
        $donation->sum_of_string = $data['sum_of_string'];
        $donation->receipt_id = $this->model->getReceiptId();

        //Payment
        $card = new CreditCardData();
        $card->number = $data['card_number'];
        $card->expMonth = $data['expiry_date'];
        $card->expYear = $data['expiry_year'];
        $card->cvn = $data['cvn'];
        $card->cardHolderName = $data['card_holder_name'];

        // Create Address instance
        $address = new Address();
        $address->streetAddress1 = $data['address1'];
        $address->streetAddress2 = $data['address2'] ?? NULL;
        $address->city = $data['city'];
        $address->state = $data['province'];
        $address->postalCode = $data['postal_code'];
        $address->country = $data['country'];

        try {

            $responseCharge = $card->charge(round($data['amount'], 2))
                    ->withCurrency('CAD')
                    ->withDynamicDescriptor('HUMANITY FIRST CANADA')  //Consult with Corex  
                    ->withAllowDuplicates(false)
                    ->withClientTransactionId("HF-" . $donation->receipt_id)
                    ->withAddress($address)
                    ->execute();
            $fObj = \App\Config\Pixel::flattenObject($responseCharge);
            $donation->cheque_trans_no = $fObj['transactionReference_transactionId'];
            $donation->is_online = '1'; //$fObj['']

            $this->model->addDonation($donation);
            $responseBody = [
                'transaction_id' => $responseCharge->transactionId,
                'amount' => $responseCharge->authorizedAmount,
                'batch_id' => $responseCharge->batchSummary->batchReference,
                'transaction_type' => $responseCharge->originalTransactionType,
                'reference_number' => $responseCharge->referenceNumber,
                'transaction_status' => $responseCharge->responseMessage,
                'time_created' => $responseCharge->timestamp,
                'response_code' => $responseCharge->responseCode,
                'card_brand_reference' => $responseCharge->cardBrandTransactionId,
                'authorization_code' => $responseCharge->authorizationCode,
                'avs_response_code' => $responseCharge->avsResponseCode,
                'avs_address_response' => $responseCharge->avsAddressResponse,
                'cvn_response_message' => $responseCharge->cvnResponseMessage,
                'card_type' => $responseCharge->cardDetails->brand,
                'masked_number_last4' => $responseCharge->cardDetails->maskedNumberLast4,
                'card_issuer_result' => $responseCharge->cardIssuerResponse->result,
                'fraud_response_mode' => $responseCharge->fraudFilterResponse->fraudResponseMode,
                'fraud_response_result' => $responseCharge->fraudFilterResponse->fraudResponseResult,
            ];

            // Send the successful response
            $response->getBody()->write(json_encode(ApiResponse::success($fObj)));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            print_r($ex);
            $response->getBody()->write(json_encode(ApiResponse::notFound(["message" => "Transaction error: " . $ex->getMessage()])));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error(["message" => "Transaction error: " . $e->getMessage()])));
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
