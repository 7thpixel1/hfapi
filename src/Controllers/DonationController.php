<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PixelModel;
use App\Config\ApiResponse;
use Mpdf\Mpdf;

class DonationController extends BaseController {

    public function __construct(PixelModel $model) {
        $this->model = $model;
    }

    public function getDonations(Request $request, Response $response, $args) {
        $donor_id = $request->getAttribute('user_id');
        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $limit = 100;
        $this->model->setLimit($limit);
        $this->model->setOffset($page);
        $donations = $this->model->donations((object) ["id" => $donor_id]);
        $count = $this->model->donationsCount((object) ["id" => $donor_id]);

        if ($donations !== null) {
            $response->getBody()->write(json_encode(ApiResponse::success(["donations" => $donations, "count" => $count])));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::success(["donations" => null, "count" => 0])));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }

    public function getDonation(Request $request, Response $response, $args) {
        $donor_id = $request->getAttribute('user_id');
        $donation_id = $args['donation_id'];
        $queryParams = $request->getQueryParams();
        $is_duplicate = isset($queryParams['isd']) ? (int) $queryParams['isd'] : 1;

        $donation = $this->model->getDonation((object) ["id" => $donation_id, "donor_id" => $donor_id]);

        if ($donation) {
            $donor = $this->model->getDonor($donor_id);
            $pdfContent = $this->generateDonationPDF($donation, $donor);
            // Return the PDF response
            $response = $response->withHeader('Content-Type', 'application/pdf');
            $response->getBody()->write($pdfContent);

            return $response;
//            $response->getBody()->write(json_encode(ApiResponse::success(["donation" => $donation])));
//            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::notFound("Donation not found!")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    public function sendDonation(Request $request, Response $response, $args) {

        $donor_id = $args['donor_id'];
        $donation_id = $args['donation_id'];
        $queryParams = $request->getQueryParams();
        $is_duplicate = isset($queryParams['isd']) ? (int) $queryParams['isd'] : 1;
        $donation = $this->model->getDonation((object) ["id" => $donation_id, "donor_id" => $donor_id]);
        if ($donation) {
            $donor = $this->model->getDonor($donor_id);

            $heading = 'Thank you for your donation';
            $name = $donor->first_name;
            $message = 'We have received your donation, thank you for supporting Humanity First. Your Official Receipt for income tax purposes is attached.';
            $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => $name, 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
            $pdfContent = $this->generateDonationPDF($donation, $donor);
            $emailObject = new \stdClass();
            $emailObject->to = $donor->email;
            $emailObject->subject = $heading;
            $emailObject->body = $html;
            $emailObject->pdfFilename = "HFC-" . $donation->receipt_id . ".pdf";
            $emailObject->pdfContent = $pdfContent;

            $success = 202; //\App\Config\Pixel::sendEmailWithSendGrid($emailObject);
            if ($success === 202) {
                $response->getBody()->write(json_encode(ApiResponse::success()));
            } else {
                $response->getBody()->write(json_encode(ApiResponse::error("email not sent")));
            }
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::notFound("Donation not found!")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    private function generateDonationPDF($donation, $donor, $is_duplicate = 1) {

        $receipt = $this->model->getReceipt($donation->receipt_id);
        $children = $this->model->getChildren($donation->id);
        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/receipt.php', ['donation' => $donation, 'donor' => $donor, 'receipt' => $receipt, 'children' => $children]);

        $mpdf = new Mpdf([
            'format' => 'A4', // You can change the format as needed
            'margin_left' => 7,
            'margin_right' => 7,
            'margin_top' => 7,
            'margin_bottom' => 0,
        ]);

        $mpdf->SetHTMLFooter('
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 10px; border-top: 0px;">
        <tr>
            <!-- Text on the left side -->
            <td style="text-align: left;">
                INCOME TAX DEDUCTIBLE - Official Receipt for Income Tax Purposes Registration No. 87254 1040 RR0001<br>
(Under Charitable Organization Act)Humanity First is a Registered Trade Mark For information on all registered<br>
charities in Canada under the income tax act, please contact<br>
Canada Revenue Agency at www.cra-arc.gc.ca/charitiesandgiving</td>
            <td style="text-align: right; width:165px;">
                <img src="./assets/images/signs.png" alt="signs" width="165px" height="101" />
            </td>
            <td width="30%" style="text-align: right; width:50px;">
                <br><br><br><br><br>{PAGENO} of {nbpg}
                
            </td>
            <tr>
            
    </table>
');
        if ((int) $is_duplicate >= 1) {
            $mpdf->SetWatermarkImage('./assets/images/duplicate.png', 0.5, true); // 0.1 is the opacity, true for all pages
            $mpdf->showWatermarkImage = true;
        }
        $mpdf->WriteHTML($html);

        // Output the PDF to a string
        return $mpdf->Output('', 'S');
    }

    public function annualStatement(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
        try {
            
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function recurringDonations(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
        try {
            
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
