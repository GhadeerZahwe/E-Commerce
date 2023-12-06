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

    // Check if the user has permission to add a product (user type id 1 for sellers)
    if ($decoded->usertype_id == 1) {
        // Assuming you're using POST method to send data
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];

        $query = $mysqli->prepare('INSERT INTO products (name, description, price, stock_quantity) VALUES (?, ?, ?, ?)');
        $query->bind_param('ssdi', $name, $description, $price, $stock_quantity);

        $response = array();

        if ($query->execute()) {
            $response["status"] = "success";
            $response["message"] = "Product added successfully!";
        } else {
            $response["status"] = "error";
            $response["message"] = "Error adding product: " . $mysqli->error;
        }

        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only sellers (usertype_id = 1) can add products.';
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
