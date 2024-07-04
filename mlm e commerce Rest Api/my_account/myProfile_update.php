<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: POST");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

    include('../db.php');

    // GET POST DATA BY FORM POST
    $postData = json_decode(file_get_contents("php://input"), true);

    //get the auth_token
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

    //send the auth_token 
    require_once '../function/verify_authToken.php';

    $updateData = [
        'mr_mrs' => mysqli_real_escape_string($con, $postData['mr_mrs']),
        'customer_name' => mysqli_real_escape_string($con, $postData['customer_name']),
        'ph_num' => mysqli_real_escape_string($con, $postData['ph_num']),
        'email_id' => mysqli_real_escape_string($con, $postData['email_id']),
        'dob' => mysqli_real_escape_string($con, $postData['dob']),
        'state' => mysqli_real_escape_string($con, $postData['state']),
        'district' => mysqli_real_escape_string($con, $postData['district']),
        'city' => mysqli_real_escape_string($con, $postData['city']),
        'pincode' => mysqli_real_escape_string($con, $postData['pincode']),
        'address' => mysqli_real_escape_string($con, $postData['address']),
        'nominee_name' => mysqli_real_escape_string($con, $postData['nominee_name']),
        'relation' => mysqli_real_escape_string($con, $postData['relation']),
        'user_id' => mysqli_real_escape_string($con, $postData['ph_num'])
    ];

    // Check if at least one field is provided for update
    if (count(array_filter($updateData)) === 0) {
        $status = "Error";
        $message = "No data provided for update";
        $response = [
            'status' => $status,
            'mssg' => $message,
        ];
        header("HTTP/1.0 200 Success");
        echo json_encode($response);
        exit;
    }

    //phone number check
    $dataget = mysqli_query($con, "select * from customer_master where ph_num='{$updateData['ph_num']}' and customer_code<>'" . $customer_code . "' ");
    $data = mysqli_fetch_row($dataget);
    if ($data) {
        $status = "ph_num Exist";
        $message = "Already Exist Same Phone Number !!";
        $response = [
            'status' => $status,
            'mssg' => $message,
        ];
        header("HTTP/1.0 200 Success");
        echo json_encode($response);
        exit;
    }

    $updateQuery = "UPDATE customer_master SET ";
    foreach ($updateData as $key => $value) {
        $updateQuery .= "$key = '$value', ";
    }
    $updateQuery = rtrim($updateQuery, ', '); // Remove the trailing comma
    $updateQuery .= " WHERE customer_code = '$customer_code'";

    $updateResult = mysqli_query($con, $updateQuery);

    if ($updateResult) {
        $status = "Success";
        $message = "Profile Details Updated Successfully";
    } else {
        $status = "Error";
        $message = "Failed to update profile details";
    }

    $response = [
        'status' => $status,
        'mssg' => $message,
    ];
    header("HTTP/1.0 200 Success");

    echo json_encode($response);
?>
