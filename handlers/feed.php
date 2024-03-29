<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

class FeedHandler {
    private string $secret_key = 'secret_key';

    public function handleRequest(): void {
        $request_method = $_SERVER['REQUEST_METHOD'];

        if ($request_method !== 'GET') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        $authorization_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (strpos($authorization_header, 'Bearer ') === 0) {
            $token = substr($authorization_header, 7);

            try {
                $decoded_token = JWT::decode($token, new Firebase\JWT\Key($this->secret_key, 'HS256'));
                
                http_response_code(200);
            } catch (Throwable $e) {
                http_response_code(401);
                error_log('Unauthorized: ' . $e->getMessage());
            }
        } else {
            http_response_code(401);
            echo 'Unauthorized: Token not found or invalid';
            exit;
        }
    }
}

$feedHandler = new FeedHandler();
$feedHandler->handleRequest();