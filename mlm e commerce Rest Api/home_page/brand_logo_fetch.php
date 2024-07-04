<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('db.php');

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

$brand_data_fetch=[];


$brand_data = mysqli_query($con, "select brand_logo from brand_logo where active='Yes' order by order_num asc");
// $brand_data_fetch = mysqli_fetch_all($brand_data);

while($row=mysqli_fetch_assoc($brand_data))
{
    $brand_data_fetch[]=$row;
}


if ($brand_data) {
    $status = "Success";
    $message = "Brand Logo Fetched Successfully";
    $SendData = $brand_data_fetch;
}
else {
    $status = "Not Found";
    $message = "Any Brand logo Not Found";
    $SendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $SendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
