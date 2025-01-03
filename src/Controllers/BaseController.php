<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Controllers;

/**
 * Description of BaseController
 *
 * @author Admin
 */
class BaseController {
    
    protected $model;
    protected function saveDonor($data) {
        
        $pwd = $data['password_hash'] ?? \App\Config\Pixel::generateRandomString();
        $passwordHash = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 10]);
        $params = (object) [
                    'title' => '',
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'] ?? NULL,
                    'business_name' => $data['copmany'] ?? NULL,
                    'gender' => (int) ($data['gender'] ?? NULL),
                    'address1' => $data['address1'],
                    'address2' => $data['address2'] ?? NULL,
                    'city' => $data['city'] ?? NULL,
                    'state' => $data['state'] ?? NULL,
                    'country' => $data['country'] ?? NULL,
                    'postal_code' => $data['postal_code'] ?? NULL,
                    'email' => $data['email'],
                    'cell' => $data['phone'] ?? NULL,
                    'type' => (strlen($data['copmany']) > 1) ? 3 : 2,
                    'source' => '2',
                    'created_date' => date('Y-m-d H:i:s'), // Current timestamp
                    'created_by' => 1,
                    'status' => (int) ($data['status'] ?? NULL),
                    'password_hash' => $passwordHash,
                    'can_login' => 1,
                    'email_status' => 0,
                    'opt_in' => (int) ($data['opt_in'] ?? NULL),
                    'meta_info' => $data['meta_info'] ?? NULL,
                    'username' => $data['email'],
                    'provider' => $data['provider'] ?? "web",
                    'provider_id' => $data['provider_id'] ?? NULL,
                    'access_token' => $data['access_token'] ?? NULL,
                    'refresh_token' => $data['refresh_token'] ?? NULL
        ];
        return $this->model->saveDonor($params);
    }
    
}
