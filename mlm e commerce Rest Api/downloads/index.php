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
	manage_downloads.download_code,
	manage_downloads.title, 
	manage_downloads.details,
	manage_downloads.download_file,
	manage_downloads.link
	from manage_downloads 
	where manage_downloads.active='Yes' order by manage_downloads.order_num ";

$dataget = mysqli_query($con, $query);
// $slider_data_fatch = mysqli_fetch_all($home_slider_data);
while ($row = mysqli_fetch_assoc($dataget)) {
    
    $data[] = [
        "download_code" => $row['download_code'],
        "title" => $row['title'],
        "details" => $row['details'],
        "download_file" => $row['download_file']!="" ? "/upload_file/download_file/".$row['download_file'] : "",
        "link" => $row['link'],
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
