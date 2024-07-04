<?php
include('../db.php');
include("../function/mlm-logic.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);


if (empty($data['customer_code'])) {
    echo json_encode(['status' => false, 'field' => 'customer_code', 'mssg' => 'Please enter customer code']);
    exit;
} else {
    $customer_code = $data['customer_code'];
}

//check customer code is exit or not
$customer_dataget = mysqli_query($con, "SELECT customer_code FROM customer_master WHERE customer_code = '" . $customer_code . "'");

if (mysqli_num_rows($customer_dataget) == 0) {
    echo json_encode(['status' => false, 'mssg' => 'Customer code not found']);
    exit;
}

if (empty($data['new_password'])) {
    echo json_encode(['status' => false, 'field' => 'new_password', 'mssg' => 'Please enter New Password']);
    exit;
} else {
    $new_password = $data['new_password'];
}

if (empty($data['confirm_password'])) {
    echo json_encode(['status' => false, 'field' => 'confirm_password', 'mssg' => 'Please enter Confirm Password']);
    exit;
} else {
    $confirm_password = $data['confirm_password'];
}

if ($new_password != $confirm_password) {
    echo json_encode(['status' => false, 'mssg' => 'password not matched']);
    exit;
} else {
    $encodePassword = base64_encode($new_password);

    mysqli_query($con, "update customer_master set password='{$encodePassword}' where customer_code='{$customer_code}'");

    echo json_encode(['status' => true, 'mssg' => 'Password update Successfully']);
}
