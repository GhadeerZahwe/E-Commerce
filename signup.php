<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include("connection.php");

// POST method to send data
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$usertype_id = $_POST['usertype_id'];  // Assuming 'usertype_id' is provided in the POST data

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = $mysqli->prepare('INSERT INTO users (username, password, email, usertype_id) VALUES (?, ?, ?, ?)');
$query->bind_param('sssi', $username, $hashed_password, $email, $usertype_id);

$response = array();

if ($query->execute()) {
    $response["status"] = "success";
    $response["message"] = "User registered successfully!";
} else {
    $response["status"] = "error";
    $response["message"] = "Error registering user: " . $mysqli->error;
}

echo json_encode($response);

$query->close();
$mysqli->close();
?>
