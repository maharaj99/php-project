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
$category_data_fatch=[];


$home_category_data = mysqli_query($con, "select category_code, category,category_img from category_master where active='Yes' order by category asc ");
// $slider_data_fatch = mysqli_fetch_all($home_slider_data);
while($row=mysqli_fetch_assoc($home_category_data))
{
    $category_data_fatch[]=$row;
}


if ($category_data_fatch) {
    $status = "Success";
    $message = "Home Category Data Fetched Successfully";
    $SendData = $category_data_fatch;
}
else {
    $status = "Not Found";
    $message = "Category Not Found";
    $SendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $SendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
