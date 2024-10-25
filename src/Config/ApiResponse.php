<?php
namespace App\Config;

class ApiResponse {
    public static function success($data = null, $message = '', $status = 200) {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function error($message = 'An exception occurred', $status = 500) {
        return [
            'status' => $status,
            'message' => $message,
            'data' => null,
            
        ];
    }

    public static function notFound($message = 'Not found') {
        return self::success(null, $message, 200);
    }

    public static function unauthorized($message = 'Unauthorized access!') {
        return self::error($message, 401);
    }
}
