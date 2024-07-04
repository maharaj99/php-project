<?php
    include('../db.php');

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
    header('Content-Type: application/json');

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

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['old_password'])) {
        echo json_encode(['status' => false, 'field'=>'old_password', 'mssg' => 'Please enter old Password']);
        exit;
    } else {
        $old_password = $data['old_password'];
        $encodePassword = base64_encode($old_password);

    }

    if (empty($data['new_password'])) {
        echo json_encode(['status' => false, 'field'=>'new_password', 'mssg' => 'Please enter New Password']);
        exit;
    } else {
        $new_password = $data['new_password'];
    }

         //check the 'password is exist or not
         $dataget = mysqli_query($con, "SELECT password FROM customer_master WHERE password='" . $encodePassword . "' AND customer_code='" . $customer_code . "' ");
       
         $dataExists = mysqli_fetch_assoc($dataget);
         
         if (!$dataExists) {
             echo json_encode(['status' => false, 'field' => '$old_password', 'message' => 'password not matched' ]);
             exit;
         }
     
    else
    {
        $encodePassword = base64_encode($new_password);

        mysqli_query($con, "update customer_master set password='{$encodePassword}' where customer_code='{$customer_code}'");

        echo json_encode(['status' => true, 'mssg' => 'Password update Successfully']);

    }
?>