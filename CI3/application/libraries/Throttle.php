<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of Throttle
 *
 * @author saqibahmad
 */
class Throttle {
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function limitRequests($limit, $interval)
    {
        $ipAddress = $this->CI->input->ip_address();
        $key = 'throttle_' . $ipAddress;

        $requests = $this->CI->cache->file->get($key);

        if ($requests === false) {
            $requests = 1;
            $this->CI->cache->file->save($key, $requests, $interval);
        } else {
            $requests++;
            $this->CI->cache->file->save($key, $requests, $interval);
        }

        if ($requests > $limit) {
            show_error('Rate limit exceeded. Please try again later.', 429);
        }
    }
}
