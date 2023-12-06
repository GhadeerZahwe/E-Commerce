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

    // Check if the user has permission to delete a product (user type id 1 for sellers)
    if ($decoded->usertype_id == 1) {
        // POST method used to send data
        $product_id = $_POST['product_id'];

        $query = $mysqli->prepare('DELETE FROM products WHERE product_id=?');
        $query->bind_param('i', $product_id);

        $response = array();

        if ($query->execute()) {
            $response["status"] = "success";
            $response["message"] = "Product deleted successfully!";
        } else {
            $response["status"] = "error";
            $response["message"] = "Error deleting product: " . $mysqli->error;
        }

        echo json_encode($response);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized. Only sellers (usertype_id = 1) can delete products.';
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
