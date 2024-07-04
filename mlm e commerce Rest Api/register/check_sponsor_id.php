<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

//database
include('../db.php');

$ref_customer_data=[];


// GET POST DATA BY FORM POST
$postData = json_decode(file_get_contents("php://input"), true);

$sponsor_id = isset($postData['sponsor_id']) ? $postData['sponsor_id'] : null;

if(empty($sponsor_id)){
    echo json_encode(['status'=>true,'mssg'=>'Give sponsor id']);
    exit;
}

// IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
if (empty($postData) && isset($_POST['sendData'])) {
    $postData = json_decode($_POST['sendData'], true);
}

// ONLY USE FOR API CHECK
if (empty($postData)) {
    $postData = $_POST;
}



$ref_customer_dataget = mysqli_query($con,"select customer_name, user_id from customer_master where ref_id='".$sponsor_id."' and active='Yes' ");
// $ref_customer_data = mysqli_fetch_row($ref_customer_dataget);

while ($row = mysqli_fetch_assoc($ref_customer_dataget)) {
    $ref_customer_data[] = $row;
}

if (!$ref_customer_data) {
	$status = 'Not Found';
	$message = 'This Ref ID Not Exist !!';
	$SendData=[];

}
else{
	$status = 'Success';
	$SendData = $ref_customer_data;
	$message = 'Sponsor Details Fatched';

}

$response = [
	'status' => $status,
	'data' =>$SendData,
	'mssg' => $message,
];

header("HTTP/1.0 200 Success");

echo json_encode($response);
?>