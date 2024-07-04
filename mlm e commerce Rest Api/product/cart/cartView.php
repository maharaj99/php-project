<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('../../db.php');

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

$auth_token = mysqli_real_escape_string($con, $auth_token);

   //send the auth_token 
   require_once '../../function/verify_authToken.php';


$productCart_data_fatch=[];

$home_productCart_data = mysqli_query($con, "SELECT 
    customer_cart.cart_code,
    customer_cart.customer_code,
    customer_cart.product_code,
    customer_cart.quantity,
    product_master.product_name,
    product_master.product_image_1,
    product_master.category_code,
    category_master.category,
    product_master.sub_category_code,
    sub_category_master.sub_category,
    product_master.mrp,
    product_master.sale_price,
    product_master.discount_percentage
FROM customer_cart
LEFT JOIN product_master ON customer_cart.product_code = product_master.product_code
LEFT JOIN category_master ON product_master.category_code = category_master.category_code
LEFT JOIN sub_category_master ON product_master.sub_category_code = sub_category_master.sub_category_code
WHERE customer_cart.customer_code = '{$customer_code}'");



while ($row = mysqli_fetch_assoc($home_productCart_data)) {
    $productCart_data_fatch[] = $row;
}

if ($productCart_data_fatch) {
    $status = "Success";
    $message = "Cart list Fetched Successfully";
    $SendData = $productCart_data_fatch;
} else {
    $status = "Not Found";
    $message = "Cart list Not Found";
    $SendData = [];
}

$response = [
    'status' => $status,
    'mssg' => $message,
    'data' => $SendData,
];
header("HTTP/1.0 200 Success");

echo json_encode($response);
?>
