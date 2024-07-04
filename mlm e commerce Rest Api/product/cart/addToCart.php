<?php
   header('content-type:application/json');
   header('Acess-control-Allow-Orifgin: *');
   header('Acess-control-Allow-Methods:post');
   header('Acess-control-Allow-Headers:Acess-control-Allow-Headers,content-type,Acess-control-Allow-Methods,Authorization,X-Requested-With');


   include('../../db.php');

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
   require_once '../../function/verify_authToken.php';

   // GET POST DATA BY FORM POST
   $postData = json_decode(file_get_contents("php://input"), true);

   if (empty( $postData['product_code'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing product code ']);   
       exit;
   }
   else {
       $product_code = $postData['product_code'];
   }

    //check product code is exit or not
    $product_dataget = mysqli_query($con, "SELECT product_code FROM product_master WHERE product_code = '".$product_code."'");

    if (mysqli_num_rows($product_dataget) == 0) {
            echo json_encode(['status' => false, 'mssg' => 'Product code not found']);
            exit;
    }

     
   if (empty( $postData['quantity'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing quantity ']);   
       exit;
   }
   else {
       $quantity = $postData['quantity'];
   }

   $cart_code = "CCC_" . uniqid() . time();

   // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
   if (empty($postData) && isset($_POST['sendData'])) {
       $postData = json_decode($_POST['sendData'], true);
   }

   // ONLY USE FOR API CHECK
   if (empty($postData)) {
       $postData = $_POST;
   }

   $dataget = mysqli_query($con, "SELECT * FROM customer_cart WHERE product_code='" . $product_code . "' AND customer_code='" . $customer_code . "'");
   $dataExists = mysqli_fetch_assoc($dataget);
   if($dataExists!=""){
    echo json_encode(['status'=>false,'mssg'=>'Already cart this product']);
    exit;

   }
   
   $sql="insert into customer_cart(cart_code,customer_code,product_code,quantity,entry_user_code) values ('{$cart_code}','{$customer_code}','{$product_code}','{$quantity}','{$customer_code}')";
   $res=mysqli_query($con,$sql);   

   if($res){

       echo json_encode(['status'=>true,'mssg'=>'Product cart Successfully']);

   }
   else{
       echo json_encode(['status'=>false,'mssg'=>'Product cart failed']);
   }
?>