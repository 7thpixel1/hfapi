<?php

namespace App\Helpers;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class LoggerHelper {

    public static function getLogger() {
        $logPath = __DIR__ . '/../../public/logs/' . date('Y-m-d') . '.txt'; 
        
        $logger = new MonologLogger('HF_API');
        $logger->pushHandler(new StreamHandler($logPath, MonologLogger::DEBUG));

        return $logger;
    }
}