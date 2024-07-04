<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: POST");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

    include('db.php');

    // GET POST DATA BY FORM POST
    $postData = json_decode(file_get_contents("php://input"), true);

    //  IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
    if (empty($postData)&& isset($_POST['sendData'])) {
        $postData = json_decode($_POST['sendData'], true);
    }

    //  ONLY USE FOR API CHECK
    if (empty($postData)) {
        $postData = $_POST;
    }
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

    $productCart_data_fatch = [];
    $productWishList_data_fatch=[];


    //send the auth_token 
    if (isset($auth_token)) 
        {
            // If Auth-Token is provided, verify it
            require_once '../function/verify_authToken.php';
            $auth_token = mysqli_real_escape_string($con, $auth_token);

            //cart list
            $home_productCart_data = mysqli_query($con, "SELECT product_code
                FROM customer_cart
                WHERE customer_cart.customer_code = '{$customer_code}'");
            while ($row1 = mysqli_fetch_assoc($home_productCart_data)) {
                $productCart_data_fatch[] = $row1;
            }

            //wish list
            $home_productwishList_data = mysqli_query($con, "SELECT product_code
            FROM customer_wishlist
            WHERE customer_wishlist.customer_code = '{$customer_code}'");
            while ($row1 = mysqli_fetch_assoc($home_productwishList_data)) {
            $productWishList_data_fatch[] = $row1;
        }


        } 

    $featured_data_fatch = [];

    $featured_product_data = mysqli_query($con,  "SELECT product_master.product_name, 
                        category_master.category, 
                        sub_category_master.sub_category, 
                        product_master.discount_percentage, 
                        product_master.sale_price, 
                        product_master.mrp,
                        product_master.product_code,
                        product_master.product_image_1, 
                        product_master.product_image_2,
                        product_master.product_image_3, 
                        product_master.product_image_4, 
                        product_master.product_image_5, 
                        (SELECT COUNT(*) FROM product_review WHERE product_code = product_master.product_code ) AS total_review
                        FROM product_master 
                        LEFT JOIN category_master ON category_master.category_code = product_master.category_code
                        LEFT JOIN sub_category_master ON sub_category_master.sub_category_code = product_master.sub_category_code 
                        WHERE product_master.active = 'Yes' AND 
                        product_master.featured = 'Yes' ");

        // Create a map of product codes in the cart & wishlist
        $cartProductCodes = array_column($productCart_data_fatch, 'product_code');
        $wishProductCodes = array_column($productWishList_data_fatch, 'product_code');
            
    // // Check for errors in the query
    // if (!$home_product_data) {
    //     $status = "Error";
    //     $message = "Query Error: " . mysqli_error($con);
    //     $SendData = [];
    // } else {
        while ($row = mysqli_fetch_assoc($featured_product_data)) {
            // Check if the product_code is in the cart and wishlist
            $row['cart'] = in_array($row['product_code'], $cartProductCodes) ? "Yes" : "No";

            $row['wish'] = in_array($row['product_code'], $wishProductCodes) ? "Yes" : "No";

            $featured_data_fatch[] = $row;
        }

        if($featured_data_fatch)
        {
            $status = "Success";
            $message = "featured Product Fetched Successfully";
            $SendData = $featured_data_fatch;
        }
        else {
            $status = "Not Found";
            $message = "featured Product Not Found";
            $SendData = [];
        }
    // }

    $response = [
        'status' => $status,
        'mssg' => $message,
        'data' => $SendData,
    ];
    header("HTTP/1.0 200 Success");

    echo json_encode($response);
?>
