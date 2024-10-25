<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PixelModel;
use App\Config\ApiResponse;
use Mpdf\Mpdf;

class DonationController {

    private $model;

    public function __construct(PixelModel $model) {
        $this->model = $model;
    }

    public function getDonations(Request $request, Response $response, $args) {
        $donor_id = $request->getAttribute('user_id');
        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $this->model->setLimit(100);
        $this->model->setOffset($page);
        $donations = $this->model->donations((object) ["id" => $donor_id]);

        if ($donations) {
            $response->getBody()->write(json_encode(ApiResponse::success(["donations" => $donations])));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(ApiResponse::notFound()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    public function getDonation(Request $request, Response $response, $args) {
        $donor_id = $request->getAttribute('user_id');
        $donation_id = $args['donation_id'];

        $donation = $this->model->getDonation((object) ["id" => $donation_id, "donor_id" => $donor_id]);

        if ($donation) {
            $donor = $this->model->getDonor($donor_id);
            $receipt = $this->model->getReceipt($donation->receipt_id);
            $children = $this->model->getChildren($donation->id);
            $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/receipt.php', ['donation' => $donation, 'donor' => $donor, 'receipt' => $receipt, 'children' => $children]);
            //$mpdf = new Mpdf();
            $mpdf = new Mpdf([
                'format' => 'A4', // You can change the format as needed
                'margin_left' => 7,
                'margin_right' => 7,
                'margin_top' => 7,
                'margin_bottom' => 0,
            ]);
            $mpdf->SetHTMLFooter('
    <table width="100%" style="font-size: 10px; border-top: 0px;">
        <tr>
            <!-- Text on the left side -->
            <td width="70%" style="text-align: left;">
                INCOME TAX DEDUCTIBLE - Official Receipt for Income Tax Purposes Registration No. 87254 1040 RR0001<br>
(Under Charitable Organization Act)Humanity First is a Registered Trade Mark For information on all registered<br>
charities in Canada under the income tax act, please contact<br>
Canada Revenue Agency at www.cra-arc.gc.ca/charitiesandgiving<br><br>{PAGENO} of {nbpg}
            </td>
            
            <!-- Image in the middle right -->
            <td width="30%" style="text-align: right;">
                <img src="./assets/images/signs.png" alt="signs" width="165px" height="101" />
            </td>
            <tr>
            
    </table>
');

            // Write the HTML to mPDF
            $mpdf->WriteHTML($html);

            // Output the PDF to a string
            $pdfContent = $mpdf->Output('', 'S');

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
}
