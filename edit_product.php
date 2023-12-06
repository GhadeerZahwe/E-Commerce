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

    // Check if the user has permission to edit a product (user type id 1 for sellers)
    if ($decoded->usertype_id == 1) {
        // POST method used to send data
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];

        $query = $mysqli->prepare('UPDATE products SET name=?, description=?, price=?, stock_quantity=? WHERE product_id=?');
        $query->bind_param('ssdii', $name, $description, $price, $stock_quantity, $product_id);

        $response = array();

        if ($query->execute()) {
            $response["status"] = "success";
            $response["message"] = "Product edited successfully!";
        } else {
            $response["status"] = "error";
            $response["message"] = "Error editing product: " . $mysqli->error;
        }

        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only sellers (usertype_id = 1) can edit products.';
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
