<?php
include("connection.php");
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

$headers = getallheaders();

if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$authorizationHeader = $headers['Authorization'];
$token = null;

$token = trim(str_replace("Bearer", '', $authorizationHeader));
if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

try {
    $key = "your_secret";
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Check if the user has permission to place an order (user type id 3 for customers)
    if ($decoded->usertype_id == 3) {
        // POST method to send data
        $user_id = $decoded->user_id;

        $response = array();
        $response["status"] = "success";
        $response["message"] = "Order placed successfully!";

        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only customers (usertype_id = 3) can place orders.';
        echo json_encode($response);
    }
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
}

$mysqli->close();
?>
