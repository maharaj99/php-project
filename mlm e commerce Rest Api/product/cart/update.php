<?php
   header('content-type:application/json');
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: PUT');
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

   if (empty($postData['cart_code'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing cart code']);
       exit;
   } else {
       $cart_code = $postData['cart_code'];
   }

   //check the cart code exit or not
      $dataget = mysqli_query($con, "SELECT cart_code FROM customer_cart WHERE cart_code='" . $cart_code . "'");
      $dataExists = mysqli_fetch_assoc($dataget);
      
      if (!$dataExists) {
          echo json_encode(['status' => false, 'field' => 'cart_code', 'message' => 'cart code not found']);
          exit;
      }

   if (empty($postData['quantity'])) {
       echo json_encode(['status' => false, 'mssg' => 'Missing quantity']);
       exit;
   } else {
       $quantity = $postData['quantity'];
   }

   // Update the quantity in the cart
   $sql = "UPDATE customer_cart SET quantity = '{$quantity}' WHERE cart_code = '{$cart_code}' AND customer_code = '{$customer_code}'";
   $res = mysqli_query($con, $sql);

   if ($res) {
       echo json_encode(['status' => true, 'mssg' => 'Quantity updated successfully']);
   } else {
       echo json_encode(['status' => false, 'mssg' => 'Quantity update failed']);
   }
?>
