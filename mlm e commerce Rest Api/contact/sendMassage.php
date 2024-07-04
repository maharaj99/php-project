<?php
   header('content-type:application/json');
   header('Acess-control-Allow-Orifgin: *');
   header('Acess-control-Allow-Methods:post');
   header('Acess-control-Allow-Headers:Acess-control-Allow-Headers,content-type,Acess-control-Allow-Methods,Authorization,X-Requested-With');


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

   //send the auth_token 
   if (isset($auth_token)) {
    // If Auth-Token is provided, verify it
    require_once '../function/verify_authToken.php';
    $auth_token = mysqli_real_escape_string($con, $auth_token);


    // Continue with the rest of your code
    } else {    
        $customer_code = null;
    }

   // GET POST DATA BY FORM POST
   $postData = json_decode(file_get_contents("php://input"), true);

   if (empty( $postData['name'])) {
    echo json_encode(['status' => false, 'mssg' => 'Name is required']);   
    exit;
    }
    else {
    $name = $postData['name'];
    }

    if (empty( $postData['email'])) {
        echo json_encode(['status' => false, 'mssg' => 'email is required']);   
        exit;
        }
        else {
            $email = $postData['email'];
    }

    if (empty( $postData['phone_number'])) {
        echo json_encode(['status' => false, 'mssg' => 'phone number is required']);   
        exit;
        }
        else {
        $phone_number = $postData['phone_number'];
        }
    

   if (empty( $postData['massage'])) {
    echo json_encode(['status' => false, 'mssg' => 'Massage Field can not be empty']);   
    exit;
    }
    else {
    $massage = $postData['massage'];
    }
     
 


   $massage_code = "UMC_".uniqid().time();

   // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
   if (empty($postData) && isset($_POST['sendData'])) {
       $postData = json_decode($_POST['sendData'], true);
   }

   // ONLY USE FOR API CHECK
   if (empty($postData)) {
       $postData = $_POST;
   }

   $sql="insert into user_massage (massage_code,name,phone_number,email,massage,entry_user_code) values ('{$massage_code}','{$name}','{$phone_number}','{$email}','{$massage}','{$customer_code}')";
   $res=mysqli_query($con,$sql);   


   if($res){

       echo json_encode(['status'=>true,'mssg'=>'massage sent successfully']);

   }
   else{
       echo json_encode(['status'=>false,'mssg'=>'massage not sent']);
   }
?>



