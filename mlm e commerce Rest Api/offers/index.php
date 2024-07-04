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

$data = [];

$query = "select 
	manage_offers.offers_code,
	manage_offers.offers_image, 
	manage_offers.product_code, 
	product_master.product_name,
    manage_offers.title,
    manage_offers.link,
    manage_offers.details,
    manage_offers.offers_time,
	manage_offers.offers_date
	from manage_offers 
	LEFT JOIN product_master ON product_master.product_code = manage_offers.product_code
	WHERE manage_offers.active='Yes' order by manage_offers.offers_date ";

$dataget = mysqli_query($con, $query);
// $slider_data_fatch = mysqli_fetch_all($home_slider_data);
while ($row = mysqli_fetch_assoc($dataget)) {
    
    $data[] = [
        "offers_code" => $row['offers_code'],
        "offers_image" => "/upload_img/offers_image/".$row['offers_image'],
        "product_code" => $row['product_code'],
        "product_name" => $row['product_name'],
        "offers_date" => $row['offers_date'],
    ];
}


if ($dataget) {
    $status = "Success";
    $message = "Data Fetched Successfully";
    $SendData = $data;
} else {
    $status = "Not Found";
    $message = "Data Not Found";
    $SendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $SendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
