<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ecommercedb';

$mysqli = new mysqli($host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    $response = array(
        "status" => "error",
        "message" => "Connection failed: " . $mysqli->connect_error,
    );
    echo json_encode($response);
    die();
} else {
    $response = array(
        "status" => "success",
        "message" => "Connection established successfully!",
    );
    echo json_encode($response);
    die();
}
