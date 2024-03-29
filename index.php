<?php

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

switch ($request_uri) {
    case '/register':
        require_once 'handlers/register.php';
        break;
    case '/authorize':
        require_once 'handlers/authorize.php';
        break;
    case '/feed':
        require_once 'handlers/feed.php';
        break;
    default:
        http_response_code(404);
        echo 'Not Found';
        break;
}
