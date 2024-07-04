<?php
include('db.php'); // Include your database connection file

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['ph_num']) || empty($data['password'])) {
    echo json_encode(['status' => false, 'mssg' => 'Missing phone number or password']);
    exit;
} else {
    $ph_num = $data['ph_num'];
    $password = base64_encode($data['password']);
}

// Check if the user exists
$loginQuery = "SELECT * FROM customer_master WHERE ph_num = '$ph_num' AND password = '$password'";
$loginResult = mysqli_query($con, $loginQuery);

if (!$loginResult) {
    echo json_encode(['status' => false, 'mssg' => 'Login failed. Please try again.']);
    exit;
}

$userData = mysqli_fetch_assoc($loginResult);

if (!$userData) {
    echo json_encode(['status' => false, 'mssg' => 'Invalid phone number or password']);
    exit;
}

// Generate authentication token
$authToken = bin2hex(random_bytes(32));

// Insert auth token into auth_tokens table
$insertAuthTokenQuery = "INSERT INTO auth_tokens (user_id, auth_token) VALUES ('{$userData['user_id']}', '$authToken')";
$insertAuthTokenResult = mysqli_query($con, $insertAuthTokenQuery);

if (!$insertAuthTokenResult) {
    echo json_encode(['status' => false, 'mssg' => 'Error generating auth token. Please try again.']);
    exit;
}

// Respond with success and auth token
echo json_encode(['status' => true, 'auth_token' => $authToken, 'mssg' => 'Login successful']);
?>


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
        'message' => 'Please provide both user ID and password.',
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
    'message' => $message,
    'token' => isset($token) ? $token : null,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);

