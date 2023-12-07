<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include("connection.php");
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

$headers = getallheaders();

if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$authorizationHeader = $headers['Authorization'];
$token = null;

$token = trim(str_replace("Bearer ", '', $authorizationHeader));
if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

try {
    $key = "your_secret";
    $decoded = JWT::decode($token, $key, ['HS256']); 

    // Check if the user has permission to get order history
    // Allow only customers (usertype_id = 3)
    if ($decoded->usertype_id == 3) {
        // GET method to retrieve data
        $query = "
            SELECT
                oh.history_id,
                o.order_id,
                o.order_date,
                p.name AS product_name,
                oh.quantity
            FROM
                order_history oh
                JOIN orders o ON oh.order_id = o.order_id
                JOIN products p ON oh.product_id = p.product_id
            WHERE
                o.user_id = ?
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $decoded->user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $orderHistory = array();

        while ($row = $result->fetch_assoc()) {
            $orderHistory[] = $row;
        }

        $response['status'] = 'success';
        $response['order_history'] = $orderHistory;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only customers (usertype_id = 3) can get order history.';
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
