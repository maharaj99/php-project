<?php
include('../db.php');
include("../function/mlm-logic.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['ph_num'])) {
    echo json_encode(['status' => false, 'mssg' => 'Please enter your phone number']);
    exit;
} else {
    $ph_num = $data['ph_num'];
}


if (empty($data['otp'])) {
    echo json_encode(['status' => false, 'mssg' => 'Please enter your OTP']);
    exit;
} else {
    $otp = $data['otp'];
}


// CHECK ph_num 
$dataget = mysqli_query($con, "SELECT otp, phone_num FROM otp_master WHERE phone_num='" . $ph_num . "'");
$dataExists = mysqli_fetch_assoc($dataget);

if ($dataExists) {
    // Check if OTP matches
    $storedOtp = $dataExists['otp'];
    if ($otp == $storedOtp) {
        $status = 'Success';
        $message = 'OTP Matched Successfully';
    } else {
        $status = 'Failed';
        $message = 'OTP Not Matched';
    }
} else {
    $status = 'Failed';
    $message = 'Data not found';
}

$response = [
    'status' => $status,
    'mssg' => $message,
];

header("HTTP/1.0 200 Success");

echo json_encode($response);
