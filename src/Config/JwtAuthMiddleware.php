<?php

namespace App\Config;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;
use App\Config\ApiResponse;

class JwtAuthMiddleware implements MiddlewareInterface {

    private $secretKey;

    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(ApiResponse::unauthorized('Missing authorization header!')));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
            try {
                // Decode JWT
                $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
                $user_id = $decoded->user_id;
                $request = $request->withAttribute('user_id', $user_id);
                
                return $handler->handle($request);
            } catch (\Exception $e) {
                $response = new SlimResponse();
                $response->getBody()->write(json_encode(ApiResponse::unauthorized('Invalid token!')));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        }

        $response = new SlimResponse();
        $response->getBody()->write(json_encode(ApiResponse::unauthorized('Authorization header must be in the format: Bearer <token>')));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
