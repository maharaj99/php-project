<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('../db.php');

// get the auth_token
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

// send the auth_token
require_once '../function/verify_authToken.php';


$productCart_data_fatch = [];

$cart_code = isset($postData['cart_code']) ? $postData['cart_code'] : null;


// Your additional conditions from the provided code
$queryAdd = '';
if ($cart_code != "") {
    $queryAdd = " and customer_cart.cart_code='" . $cart_code . "' ";
}

mysqli_query($con, "update customer_cart set purchase='No' where customer_code='" . $customer_code . "' ");

$total_price = 0;
$shipping_price = 0;
$total_amount = 0;

$home_productCart_data = mysqli_query($con,
    "SELECT                                                                   
        customer_cart.cart_code,
        customer_cart.product_code,
        customer_cart.quantity,
        product_master.product_name,
        product_master.unit,
        product_master.sale_price,
        product_master.courier_charges,
        (SELECT (SUM(in_quantity) - SUM(out_quantity)) FROM product_trans WHERE product_trans.product_code = customer_cart.product_code) AS current_stock
    FROM customer_cart
    LEFT JOIN product_master ON product_master.product_code = customer_cart.product_code
    WHERE customer_cart.customer_code = '{$customer_code}'
    ORDER BY customer_cart.entry_timestamp DESC;
");

if (!$home_productCart_data) {
    die('Error in query: ' . mysqli_error($con));
}

while ($row = mysqli_fetch_assoc($home_productCart_data)) {
    $totalPrice = 0;
    $shippingPrice = 0;
    $totalAmount = 0;

    $totalPrice = $row['sale_price'] * $row['quantity'];
    $shippingPrice = $row['courier_charges'];
    $totalAmount = $totalPrice + $shippingPrice;

    $total_price += $totalPrice;
    $shipping_price += $shippingPrice;
    $total_amount += $totalAmount;

    $productCart_data_fatch[] = $row;

    // Update purchase status
    mysqli_query($con, "update customer_cart set purchase='Yes' where cart_code='" . $row['cart_code'] . "'");
}

if ($productCart_data_fatch) {
    $status = "Success";
    $message = "Cart list Fetched Successfully";
    $SendData = [
        'total_price' => $total_price,
        'shipping_price' => $shipping_price,
        'total_amount' => $total_amount,
        'cart_details' => $productCart_data_fatch,
    ];
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
