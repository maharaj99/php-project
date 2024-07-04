<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('../db.php');

// GET POST DATA BY FORM POST
$postData = json_decode(file_get_contents("php://input"), true);

// IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
if (empty($postData) && isset($_POST['sendData'])) {
    $postData = json_decode($_POST['sendData'], true);
}

// ONLY USE FOR API CHECK
if (empty($postData)) {
    $postData = $_POST;
}

$system_info_data = mysqli_query($con, "SELECT email, address, ph_num FROM system_info");
$system_info_data_fetch = mysqli_fetch_assoc($system_info_data);

if ($system_info_data) {
    $status = "Success";
    $message = "contact details Fetched Successfully";
    $sendData = $system_info_data_fetch;
} else {
    $status = "Not Found";
    $message = "contact  Data Not Found";
    $sendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $sendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
?>
