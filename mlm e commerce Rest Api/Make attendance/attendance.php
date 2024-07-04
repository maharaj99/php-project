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

    require_once '../function/verify_authToken.php';





    $attendance_code = isset($postData['attendance_code']) ? $postData['attendance_code'] : null;

    if (empty($attendance_code)) {
        echo json_encode(['status' => false, 'field' => 'attendance_code', 'mssg' => 'attendance code is blank']);
        exit;
    }


    // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
    if (empty($postData)&& isset($_POST['sendData'])) {
        $postData = json_decode($_POST['sendData'], true);
    }


    // ONLY USE FOR API CHECK
    if (empty($postData)) {
        $postData = $_POST;
    }
        //attendance_code fetch form attendance_timing table 
        $dataget = mysqli_query($con, "SELECT * FROM attendance_timing WHERE otp='" . $attendance_code . "'");

        $attendance_data = mysqli_fetch_assoc($dataget);
        if (!$attendance_data) {
            echo json_encode(['status' => false, 'mssg' => 'Invalid attendance code']);
            exit;
        }
        $timestamp = date("Y-m-d H:i:s");
        // $current_time = time();
        $otp_validate_time = strtotime($attendance_data['otp_validate_time']);
    
        if ($timestamp > $otp_validate_time) {
            echo json_encode(['status' => false, 'mssg' => 'OTP validation time has expired']);
            exit;
        }
        else
        {
            $attendance_code = "AC_" . uniqid() . time();
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $active="Yes";


            $sql="insert into attendance_master
            (attendance_code,customer_code,date,time,active) values ('{$attendance_code}','{$customer_code}','{$date}','{$time}','{$active}')";
            $res=mysqli_query($con,$sql);   
         
            if($res)
            {
         
                echo json_encode(['status'=>true,'mssg'=>'Attendence Submit swucessfully']);
         
            }
            else
            {
                echo json_encode(['status'=>false,'mssg'=>'Attendence failed']);
            }
        }
        
?>



