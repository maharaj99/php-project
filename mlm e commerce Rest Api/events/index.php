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
	manage_events.event_code,
	manage_events.title, 
	manage_events.details,
	manage_events.event_image,
	manage_events.link,
    manage_events.event_time,
	manage_events.event_date
	from manage_events 
	where manage_events.active='Yes' order by manage_events.event_date ";

$dataget = mysqli_query($con, $query);
// $slider_data_fatch = mysqli_fetch_all($home_slider_data);
while ($row = mysqli_fetch_assoc($dataget)) {

    $data[] = [
        "event_code" => $row['event_code'],
        "title" => $row['title'],
        "details" => $row['details'],
        "event_image" => "/upload_img/event_image/" . $row['event_image'],
        "link" => $row['link'],
        "event_date" => $row['event_date'],
        "event_time" => $row['event_time'],

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
