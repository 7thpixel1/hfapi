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
class ApiController extends PIXEL_Controller{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        echo "Bismillah";
    }
    protected function checkRESTAuthorization() {
        $company = $this->getCompany();
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $this->_realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h2>Un-Authorized Access!</h2>';
            exit;
        }
        $user = Pixel::clean($_SERVER['PHP_AUTH_USER']); //Only AplhaNum Allowed
        $pwd = $_SERVER['PHP_AUTH_PW'];

        //update with authentication query
        if (base64_encode($user .":".$pwd) != base64_encode($company->api_user.":".$company->api_pwd)) {
            $this->unAuthorized();
        }
    }
    protected function unAuthorized() {
        header('HTTP/1.0 401 Unauthorized');
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["status" => 401, "message" => 'Un-Authorized Access!']);
        exit;
    }
}
