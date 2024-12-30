<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\ErrorHandler;
use Psr\Log\LoggerInterface;

class CustomErrorHandler extends ErrorHandler {
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    protected function logError(string $error): void {
        // Log the error message
        $this->logger->error($error);
    }

    protected function respond(): ResponseInterface {
        $response = $this->response;
        $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    protected function logException(\Throwable $exception): void {
        // Log additional exception details if needed
        $this->logger->error($exception->getMessage(), [
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}