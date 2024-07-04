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

    $auth_token = mysqli_real_escape_string($con, $auth_token);

    //send the auth_token 
    require_once '../function/verify_authToken.php';


    $productOrder_data_fatch=[];

    $home_productOrder_data = mysqli_query($con, "select 
                                                order_booking.order_code,
                                                order_booking.order_num,
                                                order_booking.product_code,
                                                product_master.product_name,
                                                product_master.product_image_1,
                                                order_booking.quantity,
                                                order_booking.price,
                                                order_booking.amount,
                                                order_booking.shipping,
                                                order_booking.shipping_charges,
                                                order_booking.total_amount,
                                                order_booking.status,
                                                order_booking.payment_method,
                                                order_booking.trans_id,
                                                order_booking.trans_img,
                                                order_booking.trans_date,
                                                order_booking.booking_date,
                                                order_booking.sale_voucher,
                                                order_booking.note,
                                                voucher_master.voucher_code
                                                from order_booking 
                                                LEFT JOIN product_master ON product_master.product_code = order_booking.product_code
                                                LEFT JOIN voucher_master ON voucher_master.order_code = order_booking.order_code
                                                where order_booking.customer_code='{$customer_code}' order by order_booking.booking_date DESC ");

    while ($row = mysqli_fetch_assoc($home_productOrder_data)) {
        $productOrder_data_fatch[] = $row;
    }

    if ($productOrder_data_fatch) {
        $status = "Success";
        $message = "Order product list Fetched Successfully";
        $SendData = $productOrder_data_fatch;
    } else {
        $status = "Not Found";
        $message = "Order list Not Found";
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
