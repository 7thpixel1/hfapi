<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PixelModel;
use App\Config\ApiResponse;
use Firebase\JWT\JWT;

class AuthController {

    private $model;

    public function __construct(PixelModel $model) {
        $this->model = $model;
    }

    public function login(Request $request, Response $response) {
        $secretKey = $_ENV['secret_key'];

        $body = $request->getParsedBody();
        $username = $body['username'];
        $password = $body['password'];

        $donor = $this->model->isAuthorized($username, $password);

        if ($donor !== null) {
            $issuedAt = time();
            $expirationTime = $issuedAt + (3600 * (int) $_ENV['TOKEN_LIFE']);
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'user_id' => $donor['id'],
            ];

            // Encode JWT token
            $jwt = JWT::encode($payload, $secretKey, 'HS256');
            $response->getBody()->write(json_encode(ApiResponse::success(['token' => $jwt, 'donor' => $donor])));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(ApiResponse::unauthorized()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    public function serverToken(Request $request, Response $response) {
        $secretKey = $_ENV['secret_key'];
        $payload = [
            'role' => 'server',
            'type' => 'non-expiring'
        ];
        $serverToken = JWT::encode($payload, $secretKey, 'HS256');
        $response->getBody()->write(json_encode(['server_token' => $serverToken]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
