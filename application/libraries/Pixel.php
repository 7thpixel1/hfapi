<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pixel
 *
 * @author Saqib Ahmad
 */
class Pixel {

    public static $INACTIVE = 0;
    public static $ACTIVE = 1;
    public static $YES = 1;
    public static $NO = 0;
    public static $ZERO = 0;
    public static $REGEX_SAFE_NO_TAG = 'A-Za-z0-9±ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİı“”~«»–—œ™®©:,\x22\^\{\}\[\]\.\-_=;!\+@\$\*\?#%&\/\(\)\'\s' . "\\\\";
    public static $REGEX_SAFE_ALPHANUMERIC = 'A-Za-z0-9±ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\.\-_\'\s';
    public static $REGEX_SAFE_ALPHA = 'A-Za-zÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\.\-_\'\s';
    public static $REGEX_SAFE_ADDRESS2 = 'A-Za-z0-9ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ:\#\/\\\(\)\.\-_\'\s';
    //public static $REGEX_SAFE_PWD = '(?=.*\d)(?!.*[\s\x22\x27`~%|&|\?\/\[\]{}<>\\\\])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{6,16}';
    public static $REGEX_SAFE_PWD = '(?=^.{6,16}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
    public static $REGEX_SAFE_SLUG = 'A-Za-z0-9ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ\-_';
    public static $REGEX_SAFE_POSTAL_ZIP = 'A-Za-z0-9\-\s';
    public static $REGEX_POSTAL_CODE = '^[A-Z]\d[A-Z][ ]?\d[A-Z]\d$';
    public static $REGEX_ZIP_CODE = '(\d{5}([\-]\d{4})?)';
    public static $REGEX_PHONE = '\+?0-9\-\(\)\s';
    public static $REGEX_MODEL = 'A-Za-z0-9ÀÂÆÇÈÉÊËÎÏÔŒÙÛÜŸàâæçèéêëîïôœùûüÿğŞşöÖĞiİ\-\s\.';
    public static $REGEX_NUMBER_AND_FLOAT = '\d*\.?\d*';
    public static $REGEX_USERNAME = 'A-Za-z0-9\-_=!\.@';
    

    public static function echoString($str) {
        echo Pixel::String($str);
    }

    public static function String($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    public static function formatDate($date = NULL, $type = "LONG") {
        $date = ($date === NULL) ? time() : strtotime($date);
        switch ($type) {
            case 'SHORT':
                return date('M j, Y', $date);
            case 'DATE_TIME':
                return date('d/m/Y H:i:s', $date);
            case 'TIME':
                return date('Y-m-d\TH:i:s', $date);
            case 'TIME2':
                return date('Y-m-d g:i:s A', $date);
            case 'NUM':
                return date('Y/m/d', $date);
            case 'NUM2':
                return date('d/m/Y', $date);
            case 'ZONE':
                return date('j M Y H:i \E\S\T', $date);
            default:
                return date('j M Y H:i', $date);
        }
    }

    public static function paginationConfig($url, $count, $limit, $segment = 3) {

        $config = array();
        $config["base_url"] = base_url($url);
        $config["total_rows"] = $count;
        //$config['use_page_numbers'] = TRUE;
        $config['num_links'] = 10;
        $config['num_links'] = 5;
        $config["per_page"] = $limit;
        $config['reuse_query_string'] = TRUE;
        $config['enable_query_strings'] = TRUE;
        $config["uri_segment"] = $segment;
        $config['full_tag_open'] = "<ul class='pagination'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";

        return $config;
    }

    public static function getSanitizedIP($ip = "") {
        $ip = (empty($ip)) ? ($_SERVER['REMOTE_ADDR'] ?? NULL) : $ip;
        $ip = Pixel::sanitizeInput($ip);
        if (!filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return $ip;
        }
        return "0.0.0.0";
    }

    public static function urlSafeString($str) {
        return preg_replace('/^-+|-+$/', '', strtolower(preg_replace('/[^\wàâæçéèêëïîôœÿüûù]+/', '-', $str)));
    }

    public static function urlSlugString($str) {
        $str = preg_replace("/(™|®|©|&trade;|&reg;|&copy;|&#8482;|&#174;|&#169;)/", "", $str);
        return self::urlSafeString($str);
    }

    public static function safeString($str) {
        return preg_replace("/[^A-Za-z0-9 ]/", ' ', $str);
    }

    /**
     * Sanitzed Input
     * 
     * This function will return sanitized string,
     * This function will allow most uzed characters including french characters, but dissallow
     * most dangerous chars and sequences
     * 
     * @param string
     * @return string  
     */
    public static function sanitizeInput($str) {
        $tags = array("shell_exec", "eval(", "system(", "passthru(", "exec(", "../", "..\\");
        $replacement = array("", "", "", "", "", "[removed]", "[removed]");
        $str = str_replace($tags, $replacement, $str);
        return preg_replace('/[^' . self::$REGEX_SAFE_NO_TAG . ']+/i', '[removed]', $str);
    }

    public static function echoJson($object) {
        if (is_object($object) || is_array($object)) {
            echo self::json($object);
        } else {
            throw new Exception('input is not either an object or array. ');
        }
    }

    public static function setMessage($message, $type = 'message') {
        $ci = &get_instance();
        $ci->session->set_userdata(array($type => $message));
    }

    /**
     * Escape Json
     * 
     * This function will return an HTML escaped json that is generally XSS safe. 
     * 
     * @param $object Object
     * @return String
     */
    public static function json($object) {
        if (is_object($object) || is_array($object)) {
            return json_encode($object, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        }
        throw new Exception('input is not either an object or array. ');
    }

    public static function generateRandomString() {
        $pool[0] = '-_';
        $pool[1] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = str_replace(array('+', '/', '='), array('a', 'A', ''), base64_encode(openssl_random_pseudo_bytes(8)));
        $second = intval(date('s'));
        $cap = substr($pool[1], (($second % 26)), 1);
        $special = substr($pool[0], (($second % 10)), 1);

        return str_shuffle($password . $special . $cap);
    }

    public static function generateRandomUserName() {
        $pool[0] = '_';
        $pool[1] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = str_replace(array('+', '/', '='), array('a', 'A', ''), base64_encode(openssl_random_pseudo_bytes(8)));
        $second = intval(date('s'));
        $cap = substr($pool[1], (($second % 26)), 1);
        $special = substr($pool[0], (($second % 10)), 1);

        return str_shuffle($password . $special . $cap);
    }

    public static function generatePassword() {
        $pool[0] = '!@#$*()!@*';
        $pool[1] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = str_replace(array('+', '/', '='), array('a', 'A', ''), base64_encode(openssl_random_pseudo_bytes(8)));
        $second = intval(date('s'));
        $cap = substr($pool[1], (($second % 26)), 1);
        $special = substr($pool[0], (($second % 10)), 1);

        return str_shuffle($password . $special . $cap);
    }

    public static function formatCurrency($amount, $decimal = 2) {
        setlocale(LC_MONETARY, 'en_US');
        if ((float) $amount >= 0) {
            return '$' . number_format($amount, $decimal, '.', '');
        } else {
            return '$(' . number_format(abs($amount), $decimal, '.', '') . ')';
        }
    }

    public static function formatCurrencyComma($amount, $decimal = 2) {
        setlocale(LC_MONETARY, 'en_US');
        if ((float) $amount >= 0) {
            return number_format($amount, $decimal, '.', ',');
        } else {
            return '(' . number_format(abs($amount), $decimal, '.', ',') . ')';
        }
    }

    public static function formatCurrencySimple($amount, $decimal = 2) {
        $amount = (stripos($amount, ".") === false) ? number_format((float) $amount, 2, '.', '') : (float) $amount;

        $rawAmount = explode('.', $amount);
        $whole = (array_key_exists(0, $rawAmount)) ? $rawAmount[0] : 0;
        $dec = (array_key_exists(1, $rawAmount)) ? $rawAmount[1] : 0;

        $decimal = (strlen($dec) < 2) ? 2 : $decimal;
        $decimal = ((float) $dec <= 0) ? 2 : strlen($dec);
        switch ($decimal) {
            case $decimal <= 2:
                $decimal = 2;

                break;
            case $decimal > 6:
                $decimal = 6;
                break;
        }

        return number_format($amount, $decimal, '.', ',');
    }

    public static function formatNumberSimple($amount) {
        return number_format($amount, 0, '.', ',');
    }

    public static function formatBoolean($val) {
        return ((int) $val === Pixel::$YES) ? 'Yes' : 'No';
    }

    public static function formatBooleanHtml($val) {
        return ((int) $val === Pixel::$YES) ? '<span class="m-l-15 label label-pill label-info-outline">Yes</span>' : '<span class="m-l-15 label label-pill label-danger-outline">No</span>';
    }

    public static function formatActiveHtml($val) {
        return ((int) $val === Pixel::$YES) ? '<span class="m-l-15 label label-pill label-info-outline">Active</span>' : '<span class="m-l-15 label label-pill label-danger-outline">In-active</span>';
    }

    public static function formatChecked($val) {
        return ((int) $val === Pixel::$YES) ? 'checked=""' : '';
    }

    

    public static function groupByArrayJSON($array, $group_key) {
        $arr = array();
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                //print_r($item);
                $arr[$item->$group_key][] = $item;
            }

            asort($arr, SORT_NUMERIC);
        }

        return $arr;
    }

    public static function groupByArray($array, $group_key, $sort = TRUE) {
        $arr = array();
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                $arr[$item->$group_key][$key] = $item;
            }
            if ($sort === TRUE) {
                asort($arr, SORT_NUMERIC);
            }
        }
        return $arr;
    }

    

    public static function pivotArraySimple($array, $group_key, $value) {
        $arr = array();
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                $arr[$item->$group_key] = $item->$value;
            }
            asort($arr, SORT_NUMERIC);
        }
        return $arr;
    }

    public static function pivotArraySimpleAccounts($array, $group_key, $value) {
        $arr = array();
        if (is_array($array)) {
            foreach ($array as $key => $item) {

                $arr[$item->$group_key] = round((float) ($arr[$item->$group_key] ?? NULL) + (float) $item->$value, 2);
            }
            asort($arr, SORT_NUMERIC);
        }
        return $arr;
    }
    public static function RestRequest($url, $http_method = "GET", $auth = NULL, $data = NULL) {
        if (!extension_loaded('curl')) {
            throw new ConnectorException('The cURL extension is required', 0);
        }
        $req = curl_init($url);
        if ($auth !== NULL) {
            curl_setopt($req, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ));
        }
        //set other curl options        
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 3);

        if (is_null($http_method)) {
            if (is_null($data)) {
                $http_method = 'GET';
            } else {
                $http_method = 'POST';
            }
        }

        //set http method in curl
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, $http_method);
        if (!is_null($data)) {
            curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($data));
        }

        //execute curl request
        $raw = curl_exec($req);

        if (false === $raw) { //make sure we got something back
            throw new Exception(curl_error($req), -curl_errno($req));
        }

        //decode the result
        $res = json_decode($raw, true);
        if (is_null($res)) { //make sure the result is good to go
            //{UPDATES handle exception for exchange rate}
            //throw new Exception('Unexpected response format', 0);
        }

        return $res;
    }
    public static function clean($string) {
        return preg_replace('/[^A-Za-z0-9\-\_\.@]/', '', $string); // Removes special chars.
    }
    
}
