<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PIXEL_Controller
 *
 * @author Saqib Ahmad
 */

class PIXEL_Controller extends CI_Controller {

    protected $pageLimit, $currentUser, $language, $enc_iv, $currencys, $company, $redirect_uri;
    public $searchObject, $version, $release, $closeDate;

    public function __construct() {
        parent::__construct();
        
    }
    public function loadModel($model) {
        $this->load->model($model, 'model');
    }
}
