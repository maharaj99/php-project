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

   if (empty( $postData['product_code'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing product code ']);   
       exit;
   }
   else {
       $product_code = $postData['product_code'];
   }

     
   if (empty( $postData['review'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing review ']);   
       exit;
   }
   else {
       $review = $postData['review'];
   }

   $review_code = "RC_" . uniqid() . time();

   // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
   if (empty($postData) && isset($_POST['sendData'])) {
       $postData = json_decode($_POST['sendData'], true);
   }

   // ONLY USE FOR API CHECK
   if (empty($postData)) {
       $postData = $_POST;
   }

   //name and email fetch form customer master 
   $dataget = mysqli_query($con, "SELECT customer_name,email_id FROM customer_master WHERE customer_code='" . $customer_code . "'");
   $dataExists = mysqli_fetch_assoc($dataget);
   $name = $dataExists['customer_name'];
   $email = $dataExists['email_id'];

   
   $sql="insert into product_review(review_code,customer_code,product_code,name,email,review,entry_user_code) values ('{$review_code}','{$customer_code}','{$product_code}','{$name}','{$email}','{$review}','{$customer_code}')";
   $res=mysqli_query($con,$sql);   

   if($res){

       echo json_encode(['status'=>true,'mssg'=>'data insert swucessfully']);

   }
   else{
       echo json_encode(['status'=>false,'mssg'=>'data not insert']);
   }
?>