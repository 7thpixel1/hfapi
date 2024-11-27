<?php

namespace App\Config;

use NumberFormatter;
use SendGrid\Mail\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    public static function clean($string) {
        return preg_replace('/[^A-Za-z0-9\-\_\.@]/', '', $string); // Removes special chars.
    }

    public static function renderView($filePath, $data = []) {
        if (is_array($data)) {
            extract($data);
        }
        ob_start();
        include $filePath;
        return ob_get_clean();
    }

    public static function flattenObject($obj, $prefix = '') {
        $result = [];
        foreach ($obj as $key => $value) {
            $fullKey = $prefix ? $prefix . '_' . $key : $key;
            if (is_object($value) || is_array($value)) {
                $result = array_merge($result, self::flattenObject($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }

    public static function convertNumberToWords($number) {

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number));
    }

    public static function encryptObject($data, $encryptionKey) {
        $cipher = "AES-256-CBC";
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $encryptedData = openssl_encrypt(json_encode($data), $cipher, $encryptionKey, 0, $iv);

        if ($encryptedData === false) {
            throw new Exception("Encryption failed.");
        }

        return base64_encode(json_encode(['iv' => base64_encode($iv), 'data' => $encryptedData]));
    }

    public static function decryptObject($encryptedPayload, $encryptionKey) {
        $cipher = "AES-256-CBC";
        $payload = json_decode(base64_decode($encryptedPayload), true);

        if (!isset($payload['iv']) || !isset($payload['data'])) {
            throw new Exception("Invalid payload structure.");
        }

        $iv = base64_decode($payload['iv']);
        $encryptedData = $payload['data'];

        $decryptedData = openssl_decrypt($encryptedData, $cipher, $encryptionKey, 0, $iv);

        if ($decryptedData === false) {
            throw new Exception("Decryption failed.");
        }

        return json_decode($decryptedData, true);
    }

    public static function metaInfo($request): array {
        $userAgent = $request->getHeaderLine('User-Agent');

        $object = [
            "ip" => $request->getServerParam('REMOTE_ADDR', '0.0.0.0'),
            "browser" => self::getBrowserName($userAgent),
            "browserVersion" => self::getBrowserVersion($userAgent),
            "isMobile" => self::isMobile($userAgent) ? 1 : 0,
            "mobile" => self::getMobileName($userAgent),
            "osName" => self::getOS($userAgent),
            "lang" => $request->getHeaderLine('Accept-Language') ?? 'en'
        ];

        return $object;
    }

// Helper Functions
    private static function getBrowserName($userAgent) {
        // Simplified browser detection logic
        if (strpos($userAgent, 'Chrome') !== false)
            return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false)
            return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false)
            return 'Safari';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false)
            return 'Internet Explorer';
        return 'Unknown';
    }

    private static function getBrowserVersion($userAgent) {
        preg_match('/(Version|Chrome|Firefox|MSIE|rv:|Trident)[\/\s](\d+)/', $userAgent, $matches);
        return $matches[2] ?? 'Unknown';
    }

    private static function isMobile($userAgent) {
        return preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent);
    }

    private static function getMobileName($userAgent) {
        if (strpos($userAgent, 'iPhone') !== false)
            return 'iPhone';
        if (strpos($userAgent, 'iPad') !== false)
            return 'iPad';
        if (strpos($userAgent, 'Android') !== false)
            return 'Android';
        return 'Unknown';
    }

    private static function getOS($userAgent) {
        if (preg_match('/Windows NT/', $userAgent))
            return 'Windows';
        if (preg_match('/Mac OS X/', $userAgent))
            return 'MacOS';
        if (preg_match('/Linux/', $userAgent))
            return 'Linux';
        if (preg_match('/Android/', $userAgent))
            return 'Android';
        if (preg_match('/iOS|iPhone|iPad/', $userAgent))
            return 'iOS';
        return 'Unknown';
    }

    public static function sendEmailWithSendGrid($object) {
        $email = new Mail();
        $email->setFrom($_ENV['APP_EMAIL_FROM'], $_ENV['APP_NAME']);
        $email->setSubject($object->subject);
        $email->addTo($object->to);
        $email->addContent("text/plain", strip_tags($object->body));
        $email->addContent("text/html", $object->body);

        // Attach the runtime-generated PDF
        $email->addAttachment(
                base64_encode($object->pdfContent),
                "application/pdf",
                $object->pdfFilename,
                "attachment"
        );
        $email->setReplyTo($_ENV['APP_EMAIL_REPLY'], $_ENV['APP_NAME']);
        $sendgrid = new \SendGrid($_ENV['SENDGRID']);
        try {
            $response = $sendgrid->send($email);
            return $response->statusCode();
        } catch (Exception $e) {
            return 0;
        }
    }

    function sendEmailWithAmazonSES($object) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $_ENV['SES_URL'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SES_USR'];
            $mail->Password = $_ENV['SES_PWD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SES_PORT'];

            // Recipients
            $mail->setFrom($_ENV['APP_EMAIL_FROM'], $_ENV['APP_NAME']);
            $mail->addAddress($object->to);

            // Add Reply-To header
            $mail->addReplyTo($_ENV['APP_EMAIL_REPLY'], $_ENV['APP_NAME']);

            // Attach the runtime-generated PDF
            $mail->addStringAttachment($object->pdfContent, $object->pdfFilename, 'base64', 'application/pdf');

            // Content
            $mail->isHTML(true);
            $mail->Subject = $object->subject;
            $mail->Body = $object->body;
            $mail->AltBody = strip_tags($object->body);

            $mail->send();
            return 202;
        } catch (Exception $e) {
            return 0;
        }
    }
}
