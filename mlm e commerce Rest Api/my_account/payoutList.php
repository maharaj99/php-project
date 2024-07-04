<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: GET");
    header("Allow: GET, OPTIONS");

    include('../db.php');

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
     
        
    $postData = json_decode(file_get_contents("php://input"), true);


        // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
        if (empty($postData)&& isset($_POST['sendData'])) {
            $postData = json_decode($_POST['sendData'], true);
        }
    
    
        // ONLY USE FOR API CHECK
        if (empty($postData)) {
            $postData = $_POST;
        }


    // GET POST DATA BY FORM POST
    $from_date = isset($postData['from_date']) ? $postData['from_date'] : "";
    $to_date = isset($postData['to_date']) ? $postData['to_date'] : "";


    // Query construction
    $query = "SELECT 
                payouts.payout_amount,
                payouts.tds_percentage, 
                payouts.tds_amount, 
                payouts.receive_amount,
                payouts.payout_date
            FROM payouts 
            WHERE payouts.customer_code='{$customer_code}' ";

    if ($from_date != "") {
        $query .= " AND payouts.payout_date >= '{$from_date}' ";
    }
    if ($to_date != "") {
        $query .= " AND payouts.payout_date <= '{$to_date}' ";
    }

    // Database query
    $result = mysqli_query($con, $query);

    $payout_data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $payout_data[] = $row;
    }

    if ($payout_data) {
        $status = "Success";
        $message = "Payout Data Fetched Successfully";
        $responseData = $payout_data;
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
