<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('db.php');

// GET POST DATA BY FORM POST
$postData = json_decode(file_get_contents("php://input"), true);

$categoryCode = isset($postData['category_code']) ? $postData['category_code'] : null;

if(empty($categoryCode)){
    echo json_encode(['status'=>false,'mssg'=>'Give category code']);
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
$SubCategory_data_fatch=[];

$home_SubCategory_data = mysqli_query($con, "SELECT 
                    product_master.category_code,
                    product_master.sub_category_code,
                    sub_category_master.sub_category_code,
                    sub_category_master.sub_category
                    FROM product_master
                    LEFT JOIN
                    sub_category_master ON sub_category_master.sub_category_code = product_master.sub_category_code
                    WHERE product_master.category_code = '{$categoryCode}' AND
                    product_master.active='Yes' ORDER BY sub_category_master.sub_category ASC");


while ($row = mysqli_fetch_assoc($home_SubCategory_data)) {
    $SubCategory_data_fatch[] = $row;
}

if ($SubCategory_data_fatch) {
    $status = "Success";
    $message = "Home Sub Category Data Fetched Successfully";
    $SendData = $SubCategory_data_fatch;
} else {
    $status = "Not Found";
    $message = "Sub Category Not Found";
    $SendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $SendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
?>
