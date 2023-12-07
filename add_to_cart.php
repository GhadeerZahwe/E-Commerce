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

    // Check if the user has permission to add to the shopping cart (user type id 3 for customers)
    if ($decoded->usertype_id == 3) {
        // POST method to send data
        $user_id = $decoded->user_id;
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        $query = $mysqli->prepare('INSERT INTO shopping_carts (user_id, product_id, quantity) VALUES (?, ?, ?)');
        $query->bind_param('iii', $user_id, $product_id, $quantity);

        $response = array();

        if ($query->execute()) {
            $response["status"] = "success";
            $response["message"] = "Product added to the shopping cart successfully!";
        } else {
            $response["status"] = "error";
            $response["message"] = "Error adding to the shopping cart: " . $mysqli->error;
        }

        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only customers (usertype_id = 3) can add to the shopping cart.';
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
