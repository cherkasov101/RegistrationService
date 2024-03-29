<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

class AuthorizeHandler {
    private string $secretKey = 'secret_key';
    private $db;

    public function __construct() {
        $db_path = __DIR__ . '/../db/users.db';
        $this->db = new SQLite3($db_path);
    }

    public function handleRequest(): void {
        $request_method = $_SERVER['REQUEST_METHOD'];

        if ($request_method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        $request_body = json_decode(file_get_contents('php://input'), true);

        if (!isset($request_body['email']) || !isset($request_body['password'])) {
            http_response_code(400);
            echo 'Bad Request';
            exit;
        }

        $email = $request_body['email'];
        $password = $request_body['password'];

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo 'Unauthorized: User not found';
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            http_response_code(401);
            echo 'Unauthorized: Invalid password';
            exit;
        }

        $user_id = $user['user_id'];
        $token_payload = [
            'user_id' => $user_id,
            'exp' => time() + 3600 
        ];
        $token = JWT::encode($token_payload, $this->secretKey, 'HS256');

        $response = [
            'access_token' => $token
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

$authorizeHandler = new AuthorizeHandler();
$authorizeHandler->handleRequest();

