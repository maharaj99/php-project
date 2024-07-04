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
     

    // GET parameters
    $type = isset($postData['type']) ? $postData['type'] : "All";
    $from_date = isset($postData['from_date']) ? $postData['from_date'] : "";
    $to_date = isset($postData['to_date']) ? $postData['to_date'] : "";



    // Query construction
    $query = "SELECT 
                customer_trans.pv,
                customer_trans.ev, 
                customer_trans.in_amount, 
                customer_trans.out_amount,
                customer_trans.payout_code,
                customer_trans.status,
                customer_trans.type, 
                customer_trans.trans_date,
                customer_master.customer_name,
                customer_master.ref_id
            FROM customer_trans 
            LEFT JOIN voucher_master ON voucher_master.voucher_code = customer_trans.voucher_code
            LEFT JOIN customer_master ON customer_master.customer_code = voucher_master.customer_code
            WHERE customer_trans.customer_code='{$customer_code}' ";

    if ($type != "All") {
        $query .= " AND customer_trans.type='{$type}' ";
    }

    if ($from_date != "") {
        $query .= " AND customer_trans.trans_date >= '{$from_date}' ";
    }
    if ($to_date != "") {
        $query .= " AND customer_trans.trans_date <= '{$to_date}' ";
    }

    // Database query
    $result = mysqli_query($con, $query);

    $trans_data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $trans_data[] = $row;
    }

    if ($trans_data) {
        $status = "Success";
        $message = "Transaction Data Fetched Successfully";
        $responseData = $trans_data;
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
