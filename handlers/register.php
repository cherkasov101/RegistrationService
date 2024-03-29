<?php

declare(strict_types=1);

require_once __DIR__ . '/../exceptions/weakPassword.php';

class RegisterHandler {
    private $db;

    public function __construct() {
        $db_path = __DIR__ . '/../db/users.db';
        $this->db = new SQLite3($db_path);
        $this->createTable();
    }

    private function createTable(): void {
        $query = 'CREATE TABLE IF NOT EXISTS users (user_id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE, password TEXT)';
        $this->db->exec($query);
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo 'Invalid email format';
            exit;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        $existing_user = $result->fetchArray(SQLITE3_ASSOC);
        if ($existing_user) {
            http_response_code(400);
            echo 'User with this email already exists';
            exit;
        }
        $answer = 'perfect';

        if (strlen($password) < 8) {
            http_response_code(400);
            throw new WeakPasswordException();
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
            $answer = 'good';
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result) {
            $user_id = $this->db->lastInsertRowID();
            $response = [
                'user_id' => $user_id,
                'password_check_status' => $answer 
            ];
            http_response_code(201);
        } else {
            $response = [
                'error' => 'Failed to register user'
            ];
            http_response_code(500);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

$registerHandler = new RegisterHandler();
$registerHandler->handleRequest();
