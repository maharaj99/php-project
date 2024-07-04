<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('db.php'); // Include your database connection file

$postData = json_decode(file_get_contents("php://input"), true);

if (empty($postData)) {
    $postData = json_decode($_POST['sendData'], true);
}

// ONLY USE FOR API CHECK
if (empty($postData)) {
    $postData = $_POST;
}

$user_id = isset($postData['user_id']) ? mysqli_real_escape_string($con, $postData['user_id']) : null;
$password = isset($postData['password']) ? mysqli_real_escape_string($con, $postData['password']) : null;

// Check if user ID or password is missing
if (empty($user_id) || empty($password)) {
    $response = [
        'status' => 'Error',
        'mssg' => 'Please provide both user ID and password.',
    ];
    header("HTTP/1.0 400 Bad Request");
    echo json_encode($response);
    exit; // Stop execution if validation fails
}

$encodePassword = base64_encode($password);

$execute = 1;

// CHECK USER ID EXIT OR NOT
$check_user_id_dataget = mysqli_query($con, "SELECT * FROM customer_master WHERE user_id = '" . $user_id . "'");
if (mysqli_num_rows($check_user_id_dataget) != 1) {
    $status = "Not Found";
    $message = "User Id Not Found";
    $execute = 0;
}

// CHECK USER CREDENTIAL
if ($execute == 1) {
    $user_dataget = mysqli_query($con, "SELECT customer_code FROM customer_master WHERE user_id = '" . $user_id . "' AND password = '" . $encodePassword . "' AND active = 'Yes'");
    $user_data = mysqli_fetch_row($user_dataget);

    if ($user_data) {
        $user_code = $user_data[0];
        $status = "Success";
        $message = "Login Successfully";
        require_once "function/jwt-token.php";
        $token = Sign(["customer_code" => $user_code]);
    } else {
        $status = "Not Match";
        $message = "Password Not Match";
    }
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'token' => isset($token) ? $token : null,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
