<?php

include("connection.php");
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

// Assuming you're using POST method to send data
$email = $_POST['email'];
$password = $_POST['password'];

$query = $mysqli->prepare('SELECT user_id, username, password, usertype_id FROM users WHERE email = ?');
$query->bind_param('s', $email);
$query->execute();
$query->store_result();
$num_rows = $query->num_rows;
$query->bind_result($user_id, $username, $hashed_password, $usertype_id);
$query->fetch();

$response = array();

if ($num_rows == 0) {
    $response['status'] = 'user not found';
    echo json_encode($response);
} else {
    if (password_verify($password, $hashed_password)) {
        $key = "your_secret";
        $payload_array = array(
            "user_id" => $user_id,
            "username" => $username,
            "usertype_id" => $usertype_id,
            "exp" => time() + 3600
        );

        $jwt = JWT::encode($payload_array, $key, 'HS256');
        
        $response['status'] = 'logged in';
        $response['jwt'] = $jwt;
        echo json_encode($response);
    } else {
        $response['status'] = 'wrong credentials';
        echo json_encode($response);
    }
}

$query->close();
$mysqli->close();
?>
