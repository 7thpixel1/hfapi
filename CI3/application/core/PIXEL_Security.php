<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PIXEL_Security extends CI_Security{
    public function __construct()
    {
        parent::__construct();
    }
    public function inValidateHash() {
        $this->_csrf_hash = NULL;
        unset($_COOKIE[$this->_csrf_cookie_name]);
        parent::csrf_set_cookie();
        
    }
    /*
    public function csrf_show_error()
    {
        header('Location: ' . htmlspecialchars($_SERVER['REQUEST_URI']), TRUE, 200);
    }*/
}
