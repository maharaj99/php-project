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

    $rankDetail_data_fatch=[];

    $rankDetails = mysqli_query($con, "select 
                                rank_code,
                                customer_code,
                                rank,
                                assign_date,
                                entry_user_code,
                                entry_timestamp,
                                update_timestamp
                                from customer_rank_details where customer_code='{$customer_code}'");

    while ($row = mysqli_fetch_assoc($rankDetails)) {
        $rankDetail_data_fatch[] = $row;
    }

    // $ref_customer_code = $profileDetail_data_fatch[0]['ref_customer_code'];
    // $under_customer_code = $profileDetail_data_fatch[0]['under_customer_code'];

    // // Fetch $ref_customer_data
    // $ref_customer_dataget = mysqli_query($con, "select customer_name, ref_id from customer_master where customer_code='" . $ref_customer_code . "' ");
    // $ref_customer_data = mysqli_fetch_assoc($ref_customer_dataget);

    // // Fetch $under_customer_data
    // $under_customer_dataget = mysqli_query($con, "select customer_name, ref_id from customer_master where customer_code='" . $under_customer_code . "' ");
    // $under_customer_data = mysqli_fetch_assoc($under_customer_dataget);

    if ($rankDetail_data_fatch) {
        $status = "Success";
        $message = "Rank Details Fetched Successfully";
        $SendData = [
            'rank_details' => $rankDetail_data_fatch
        ];
    } else {
        $status = "Not Found";
        $message = "Rank Details Not Found";
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
