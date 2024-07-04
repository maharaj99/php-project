<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: GET");
    header("Allow: GET, OPTIONS");

    include('../db.php');

// get the auth_token
function get_headers_compat() {
    if (!function_exists('getallheaders')) {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    } else {
        return getallheaders();
    }
}

$headers = get_headers_compat();

$auth_token = isset($headers['Auth-Token']) ? $headers['Auth-Token'] : null;

$auth_token = mysqli_real_escape_string($con, $auth_token);
require_once '../function/verify_authToken.php';

    
global $level;


 

    // Query construction
    $customer_query = "SELECT customer_code, customer_name, ph_num, ref_id FROM customer_master";
    
    // WHERE under_customer_code IN ({$customerCodeList})";

    // Database query
    $customer_result = mysqli_query($con, $customer_query);

    $customer_data = [];

    while ($row = mysqli_fetch_assoc($customer_result)) {
        $row['level']=$level;
        $customer_data[] = $row;
    }

    if ($customer_data) {
        $status = "Success";
        $message = "Customer Data Fetched Successfully";
        $responseData = $customer_data;
    } else {
        $status = "Not Found";
        $message = "Data Not Found";
        $responseData = [];
    }

    // Response construction
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $responseData,
    ];

    header("HTTP/1.0 200 Success");
    echo json_encode($response);
?>
