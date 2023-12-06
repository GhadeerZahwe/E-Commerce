<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

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

    // Check if the user has permission to get products (user type id 1 for sellers)
    if ($decoded->usertype_id == 1) {
        $query = "SELECT * FROM products";
        $result = $mysqli->query($query);
        $products = array();

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $response['status'] = 'success';
        $response['products'] = $products;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only sellers (usertype_id = 1) can get products.';
    }

    echo json_encode($response);
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
}

$mysqli->close();
?>
