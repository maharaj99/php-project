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

$auth_token = mysqli_real_escape_string($con, $auth_token);

   //send the auth_token 
   require_once '../function/verify_authToken.php';

   // GET POST DATA BY FORM POST
   $postData = json_decode(file_get_contents("php://input"), true);
     
   if (empty( $postData['query'])) {
       echo json_encode(['status' => false, 'mssg' => 'please Upload Your Query']);   
       exit;
   }
   else {
    $query = $postData['query'];
   }

   $query_code = "CQC_" . uniqid() . time();

   // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
   if (empty($postData) && isset($_POST['sendData'])) {
       $postData = json_decode($_POST['sendData'], true);
   }

   // ONLY USE FOR API CHECK
   if (empty($postData)) {
       $postData = $_POST;
   }

   $sql="insert into customer_query (query_code,customer_code,query,query_date,entry_user_code) values ('{$query_code}','{$customer_code}','{$query}',NOW(),'{$customer_code}')";
   $res=mysqli_query($con,$sql);   

   if($res){

       echo json_encode(['status'=>true,'mssg'=>'Query Submit successfully']);

   }
   else{
       echo json_encode(['status'=>false,'mssg'=>'Query not Submit']);
   }
?>