<?php
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

    // Check if the user has permission to get orders
    // In this case, allow only admins (usertype_id = 2)
    if ($decoded->usertype_id == 2) {
        // GET method to retrieve data
        $query = "
            SELECT
                o.order_id,
                o.order_date,
                u.username AS customer_username,
                p.name AS product_name,
                oh.quantity
            FROM
                orders o
                JOIN users u ON o.user_id = u.user_id
                JOIN order_history oh ON o.order_id = oh.order_id
                JOIN products p ON oh.product_id = p.product_id
        ";

        $result = $mysqli->query($query);
        $orders = array();

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $response['status'] = 'success';
        $response['orders'] = $orders;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only admins (usertype_id = 2) can get orders.';
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
