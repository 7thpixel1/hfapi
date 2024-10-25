<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of ApiController
 *
 * @author saqibahmad
 */
class Api extends PIXEL_Controller {

    protected $userObject;

    public function __construct() {
        parent::__construct();
        $this->loadModel('ApiModel');
    }

//    public function index() {
//        phpinfo();
//    }
    protected function checkRESTAuthorization() {

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $this->_realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h2>Un-Authorized Access!</h2>';
            exit;
        }
        $user = Pixel::clean($_SERVER['PHP_AUTH_USER']); //Only AplhaNum Allowed
        $pwd = $_SERVER['PHP_AUTH_PW'];

        $this->userObject = $this->model->isAutorized($user, $pwd);

        if ($this->userObject === NULL) {
            $this->unAuthorized();
        }
    }

    protected function unAuthorized() {
        header('HTTP/1.0 401 Unauthorized');
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["status" => 401, "message" => 'Un-Authorized Access!']);
        exit;
    }

    public function authenticate() {//13534
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();

        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $this->userObject]);
    }

    public function family() {
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();
        $list = $this->model->listFamily($this->userObject->id);
        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $list]);
    }

    public function reset_pwd() {
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();

        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $this->userObject]);
    }

    public function change_pwd() {
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();

        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $this->userObject]);
    }

    public function donations($page = 1) {
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();

        $this->model->setLimit($this->pageLimit);
        $this->model->setStart(($page - 1) * $this->pageLimit);

        $list = $this->model->donations($this->userObject);
        $count = $this->model->countDonations($this->userObject);
        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $list, "count" => $count]);
    }

    public function donate() {
        header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();

        echo json_encode(["status" => 200, "message" => 'Un-Authorized Access!', "data" => $this->userObject]);
    }

    public function statement($year) {
        $this->checkRESTAuthorization();

        $yearGet[] = $year;

        $years = array_map('intval', $yearGet);
        $yearsCrit = implode(',', $years);

        $donorId = $this->userObject->id;
        $donorObject = $this->model->getDonor($donorId);

        $donations = $this->model->getCRAAnuualStatmentList($donorId, $yearsCrit);
        $yearWiseDonations = (@count((array) $donations) > 0) ? Pixel::groupByArray($donations, "year") : NULL;

        //arsort($yearWiseDonations);
        if ($donorObject !== NULL) {

            try {
                define('K_PATH_IMAGES', FCPATH . "/assets/images/");

                define('K_FOOTER_IMAGE', K_PATH_IMAGES . "signs.png");
                require_once(FCPATH . "application/libraries/tcpdf/config/tcpdf_config.php");
                require_once(FCPATH . "application/libraries/tcpdf/tcpdf.php");
                require_once(FCPATH . "application/libraries/tcpdf/SmartPdf.php");
                $pdf = new SmartPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // set document information
                $pdf->SetCreator(PDF_CREATOR);

                $pdf->SetAuthor(COMPANY_NAME);
                $pdf->SetTitle('Anual Tax Statement for ' . $donorObject->first_name);
                $pdf->SetSubject('Tax Statement');
                $pdf->SetKeywords(COMPANY_NAME);

                $pdf->setHeaderText("Annual Donation\nStatement/Receipt");
                $pdf->setFooterData(array(20, 61, 141), array(255, 255, 255));

                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                $pdf->SetMargins(0, PDF_MARGIN_TOP, 0);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                $pdf->setFooterText(lang('report_footer'));
                $pdf->SetAutoPageBreak(TRUE, 25);
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                $pdf->setFontSubsetting(true);

                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'N', 16);

                $params = array(
                    "language" => $this->language,
                    "list" => $yearWiseDonations,
                    "donorObject" => $donorObject,
                    "heading" => "Annual Donation Statement/Receipt"
                );

                $html = $this->load->view('reports/anuual_donations_statement', $params, true);
                $pdf->writeHTML($html, true, false, true);
                ob_end_clean();
                
                $pdf->Output(FCPATH . 'assets/resource/Account-Statement-' . $donorObject->first_name . '.pdf', 'F');
                $location = './assets/resource/Account-Statement-' . $donorObject->first_name . '.pdf';
                $size = filesize($location);

                $fm = @fopen($location, 'rb');
                if (!$fm) {
                    header("HTTP/1.0 505 Internal server error");
                    return;
                }
                header('HTTP/1.0 200 OK');
                header("Content-Type: application/pdf");
                header('Cache-Control: public, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Accept-Ranges: bytes');
                header('Content-Length:' . $size);
                header("Content-Disposition: inline; filename=\"Account-Statement " . $donorObject->first_name . ".pdf\"");
                header("Content-Transfer-Encoding: binary");

                fpassthru($fm);
            } catch (Exception $exc) {
                //Silently swallo 
                echo $exc->getMessage();
                //trigger_error("un-expected error! (print receipt.)", E_USER_ERROR);
            }
        } else {
            //redirect(base_url("donors/"));
        }
    }

    public function receipt($id, $duplicate = 1) {//295695
        //header("Content-type: application/json; charset=utf-8");
        $this->checkRESTAuthorization();
        $object = new stdClass();
        $object->id = $id;
        $object->donor_id = $this->userObject->id;
        $donation = $this->model->getDonation($object);

        if ($donation === NULL) {
            header("HTTP/1.0 404 Not Found");
            return;
        } else {

            $location = $this->executePDFRoutine($donation, $duplicate);
            $size = filesize($location);

            $fm = @fopen($location, 'rb');
            if (!$fm) {
                header("HTTP/1.0 505 Internal server error");
                return;
            }
            header('HTTP/1.0 200 OK');
            header("Content-Type: application/pdf");
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Accept-Ranges: bytes');
            header('Content-Length:' . $size);
            header("Content-Disposition: inline; filename=\"" . $donation->id . ".pdf\"");
            header("Content-Transfer-Encoding: binary");

            fpassthru($fm);
        }
    }

    public function programs() {
        header("Content-type: application/json; charset=utf-8");
        //$this->checkRESTAuthorization();
        $list = $this->model->listProgram();

        echo json_encode(["status" => 200, "message" => '', "data" => $list]);
    }

    public function projects($program_id, $parent_id = 0) {
        header("Content-type: application/json; charset=utf-8");
        //$this->checkRESTAuthorization();
        $object = new stdClass();
        $object->program_id = $program_id;
        $object->parent_id = $parent_id;
        $list = $this->model->listProject($object);

        echo json_encode(["status" => 200, "message" => '', "data" => $list]);
    }

    public function branches() {
        header("Content-type: application/json; charset=utf-8");
        //$this->checkRESTAuthorization();

        $list = $this->model->listBranches();

        echo json_encode(["status" => 200, "message" => '', "data" => $list]);
    }

    /*
      if ($this->input->method() == 'put') {
      // Method logic for PUT requests
      } else {
      $this->output
      ->set_status_header(400)
      ->set_output('Bad Request: This method can only be accessed via PUT');
      }
     *      */

    private function executePDFRoutine($donation, $duplicate = 1, $output = "F") {
        try {
            define('K_PATH_IMAGES', FCPATH . "/assets/images/");
            //define('K_FOOTER_IMAGE', K_PATH_IMAGES . "blank.jpg");
            define('K_FOOTER_IMAGE', K_PATH_IMAGES . "signs.png");
            //define('K_FOOTER_IMAGE', "");
            require_once(FCPATH . "application/libraries/tcpdf/config/tcpdf_config.php");
            require_once(FCPATH . "application/libraries/tcpdf/tcpdf.php");
            require_once(FCPATH . "application/libraries/tcpdf/SmartPdf.php");

            $pdf = new SmartPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(COMPANY_NAME);
            $pdf->SetTitle('Donation Receipt');
            $pdf->SetSubject('Print Report');
            $pdf->SetKeywords(COMPANY_NAME);

            //

            $pdf->SetHeaderData("report-header-small.png", 210, "", "", array(0, 0, 0), array(196, 196, 196));
            $pdf->setFooterData(array(20, 61, 141), array(255, 255, 255));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->setFooterText(lang('report_footer'));
            $pdf->setHeaderText("DONATION RECEIPT\n" . date('Y', strtotime($donation->receipt_date)));
            $pdf->SetMargins(0, PDF_MARGIN_TOP, 0);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            //$pdf->setFontSubsetting(true);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'N', 16);

            $donor = $this->model->getDonor($donation->donor_id);
            $receipt = $this->model->getReceipt($donation->receipt_id);
            $children = $this->model->getChildren($donation->id);

            $params = array(
                "language" => $this->language,
                "donation" => $donation,
                "donor" => $donor,
                "receipt" => $receipt,
                "children" => $children,
                "heading" => "Donation Batch Report");

            $html = $this->load->view('reports/receipt', $params, true);
            if ((int) $donation->status <= 0 && (int) $duplicate > 0) {
                $img_file = K_PATH_IMAGES . 'duplicate.png';
                $pdf->Image($img_file, 50, 10, 110, 110, '', '', '', false, 300, '', false, false, 0);
            }

            if ((int) $donation->status > 0) {
                $img_file = K_PATH_IMAGES . 'void.png';
                $pdf->Image($img_file, 50, 10, 110, 110, '', '', '', false, 300, '', false, false, 0);
            }

            $pdf->writeHTML($html, true, false, true);
            $pdf->SetFont('helvetica', 'N', 16);
            $text = "Thank You";
            $pdf->MultiCell(200, 25, $text, 0, "C", false, 1, 5);
            ob_end_clean();
            if ($output === 'I') {
                $pdf->Output('hf-' . $donation->id . '.pdf', $output);
            } else {
                $pdf->Output(FCPATH . 'assets/resource/' . $donation->id . '.pdf', $output);
//                echo FCPATH . 'assets/resource/';
                return './assets/resource/' . $donation->id . '.pdf';
            }
        } catch (Exception $exc) {
            //Silently swallo 
            echo $exc->getMessage();
            //trigger_error("un-expected error! (print receipt.)", E_USER_ERROR);
        }
    }
}
