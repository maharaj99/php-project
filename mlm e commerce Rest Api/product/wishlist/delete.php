<?php
   header('content-type:application/json');
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: DELETE');
   header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With');

   include('../../db.php');

   // Get the auth_token
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

   // Send the auth_token 
   require_once '../../function/verify_authToken.php';

   // Get POST data by JSON
   $postData = json_decode(file_get_contents("php://input"), true);

   if (empty($postData['product_code'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing product code']);
       exit;
   } else {
        $product_code = $postData['product_code'];
   }

   //check the code is exixt or not
      $dataget = mysqli_query($con, "SELECT product_code FROM customer_wishlist WHERE product_code='" . $product_code . "'");
      $dataExists = mysqli_fetch_assoc($dataget);
      
      if (!$dataExists) {
          echo json_encode(['status' => false, 'field' => 'product_code', 'message' => 'product code code not found']);
          exit;
      }

   // Delete the product from the cart
   $sql = "DELETE FROM customer_wishlist WHERE product_code = '{$product_code}' AND customer_code = '{$customer_code}'";
   $res = mysqli_query($con, $sql);

   if ($res) {
       echo json_encode(['status' => true, 'mssg' => 'Product removed from whishlist successfully']);
   } else {
       echo json_encode(['status' => false, 'mssg' => 'Product removal from whishlist failed']);
   }
?>
