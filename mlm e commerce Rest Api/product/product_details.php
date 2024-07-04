<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: POST");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");


    include('../db.php');


    // GET POST DATA BY FORM POST
    $postData = json_decode(file_get_contents("php://input"), true);

    $product_code = isset($postData['product_code']) ? $postData['product_code'] : null;

    if(empty($product_code))
    {
        echo json_encode(['status'=>false,'filed'=>'product_code','mssg'=>'Product code is blank']);
        exit;

    }

    // IF FORM POST DATA BLANK THEN GET POST DATA BY AJAX
    if (empty($postData)&& isset($_POST['sendData'])) {
        $postData = json_decode($_POST['sendData'], true);
    }


    // ONLY USE FOR API CHECK
    if (empty($postData)) {
        $postData = $_POST;
    }

    $dataget = mysqli_query($con, "SELECT product_code FROM product_master WHERE product_code='" . $product_code . "'");
    $dataExists = mysqli_fetch_assoc($dataget);
    
    if (!$dataExists) {
        echo json_encode(['status' => false, 'field' => 'product_code', 'message' => 'Product code not found']);
        exit;
    }

    $cart = "No";
    $wishlist='No';

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
    if (isset($auth_token)) 
        {
            // If Auth-Token is provided, verify it
            require_once '../function/verify_authToken.php';
            $auth_token = mysqli_real_escape_string($con, $auth_token);

                    //cart list
            $home_productCart_data = mysqli_query($con, "SELECT *
                FROM customer_cart
                WHERE customer_cart.customer_code = '{$customer_code}' AND customer_cart.product_code= '{$product_code}' ");
            $productCart_data_fatch = mysqli_fetch_assoc($home_productCart_data);
            if ($productCart_data_fatch > 0) {
                $cart = "Yes";
            }

            //wish list
            $home_productwishList_data = mysqli_query($con, "SELECT *
                FROM customer_wishlist
                WHERE customer_wishlist.customer_code = '{$customer_code}' AND customer_wishlist.product_code= '{$product_code}' ");
            $productWishList_data_fatch = mysqli_fetch_assoc($home_productwishList_data);
            if ($productWishList_data_fatch > 0) {
                $wishlist = "Yes";
            }
        }
                



    


    $product_data_fatch = [];


                $sql = "select 
                product_master.product_code,
                product_master.product_name,
                category_master.category,
                sub_category_master.sub_category,
                product_master.unit,
                product_master.color,
                product_master.size,
                product_master.mrp,
                product_master.sale_price,
                product_master.discount_percentage,
                product_master.description,
                product_master.product_image_1,
                product_master.product_image_2,
                product_master.product_image_3,
                product_master.product_image_4,
                product_master.product_image_5,
                product_master.category_code,
                product_master.pv,
                product_master.ev,
                (SELECT COUNT(*) FROM product_review WHERE product_code = product_master.product_code) AS total_review
                from product_master 
                LEFT JOIN category_master ON category_master.category_code = product_master.category_code
                LEFT JOIN sub_category_master ON sub_category_master.sub_category_code = product_master.sub_category_code
                where product_master.product_code='" . $product_code . "' and product_master.active='Yes' ";



    $productList = mysqli_query($con, $sql);
    
    // Check for errors in the query
    // if (!$home_product_data) {
    //     $status = "Error";
    //     $message = "Query Error: " . mysqli_error($con);
    //     $SendData = [];
    // } else {
    while ($row = mysqli_fetch_assoc($productList)) {
        $row['cart']=$cart;
        $row['wish']=$wishlist;
        $product_data_fatch[] = $row;
    }
  


    if ($product_data_fatch) {
        $status = "Success";
        $message = "Product Details Fetched Successfully";
    } else {
        $status = "Not Found";
        $message = "Products Not Found";
    }


    // }


    $response = [
        'status' => $status,
        'mssg' => $message,
        'data' => $product_data_fatch
    ];
    header("HTTP/1.0 200 Success");


    echo json_encode($response);
?>



