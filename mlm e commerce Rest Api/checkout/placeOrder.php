<?php
    // Include your database connection file here
    include('../db.php');
    include("../function/voucher_number.php");
    
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: POST");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");
    
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
    require_once '../function/verify_authToken.php';
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'company_name', 'country', 'street_address_1', 'town', 'zip', 'state', 'ph_num', 'email', 'trans_date', 'booking_date','trans_id',];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => false, 'mssg' => "Missing $field"]);
            exit;
        }
    }

    if (empty($_FILES["trans_img"]["name"])) {
        echo json_encode(['status' => false, 'mssg' => 'Please upload a transaction image']);
        exit;
    }
    
    mysqli_query($con, "delete from customer_address where customer_code='" . $customer_code . "' ");
    
    // Insert address details
    $address_code = "CAC_" . uniqid() . time();
    
    // Extract data from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $company_name = $_POST['company_name'];
    $country = $_POST['country'];
    $street_address_1 = $_POST['street_address_1'];
    $street_address_2 = $_POST['street_address_2'];
    $town = $_POST['town'];
    $zip = $_POST['zip'];
    $state = $_POST['state'];
    $ph_num = $_POST['ph_num'];
    $email = $_POST['email'];
    $trans_date=$_POST['trans_date'];
    $booking_date=$_POST['booking_date'];
    $note = $_POST['note'];
    $shipping = $_POST['shipping'];
    $payment_method = $_POST['payment_method'];
    $trans_id = $_POST['trans_id'];

   //images upload
    $target_dir = "../../upload_content/upload_img/trans_img/";


    // Get the uploaded file information
    $trans_img_file_name = "trans_img_" . date("d-m-Y-H-i-s") . "-" . time();
    $trans_img_fl = $_FILES['trans_img']['name'];
    $original_file_name = $_FILES['trans_img']['name'];
    $ext = pathinfo($original_file_name, PATHINFO_EXTENSION);

    if (in_array($ext, $allowedImgExt)) {

        // Generate a unique file name
        $trans_img = $trans_img_file_name . "." . $ext;

        $target_file = $target_dir . $trans_img;

        // Move the uploaded file to the target directory
        move_uploaded_file($_FILES["trans_img"]["tmp_name"], $target_file);       

        $status = "Success";
        $message =" Uploaded Successfully";    
    }
    else{
        $status = "File Type Error";
        $message = "This File Type Not Acceptable";        
        exit;
    }

    
    // Insert data into the database
    $sql = "INSERT INTO customer_address (
                address_code, 
                customer_code, 
                first_name, last_name, company_name, country, street_address_1, street_address_2, town, zip, state, ph_num, email
            ) VALUES (
                '{$address_code}','{$customer_code}', '{$first_name}', '{$last_name}', '{$company_name}', '{$country}', '{$street_address_1}', '{$street_address_2}', '{$town}', '{$zip}', '{$state}', '{$ph_num}', '{$email}'
            )";
    
    $res = mysqli_query($con, $sql);

    //mysqli_error($con) to get the error message.
    // if (!$res) {
    //     echo json_encode(['status' => false, 'mssg' => 'Data not inserted. Error: ' . mysqli_error($con)]);
    //     exit;
    // }

    // var_dump($customer_code, $product_code, $quantity, $sale_price, $amount, $shipping, $shipping_charges, $total_amount, $payment_method, $trans_id, $date, $note);


        $cart_dataget = mysqli_query($con, "select 
        customer_cart.cart_code,
        customer_cart.product_code,
        customer_cart.quantity,
        product_master.product_name,
        product_master.sale_price,
        product_master.courier_charges
        from customer_cart
        LEFT JOIN product_master ON product_master.product_code = customer_cart.product_code
        where customer_cart.customer_code='" . $customer_code . "' and purchase='Yes' order by customer_cart.entry_timestamp DESC ");

        while ($rw = mysqli_fetch_array($cart_dataget)) {

        $amount = $rw['sale_price'] * $rw['quantity'];

        $product_code= $rw['product_code'];
        $quantity=$rw['quantity'];
        $sale_price=$rw['sale_price'];

        if ($shipping == "Yes") {
        $shipping_charges = $rw['courier_charges'];
        $total_amount = $amount + $shipping_charges;
        } else {
        $shipping_charges = 0;
        $total_amount = $amount;
        }

        $generate_type = 'Save';
        $voucher_type = 'Order Booking';
        $order_num = generateVoucherNumber();

        $order_code = "OC_" . uniqid() . time();
        //========================= INSERT IN TABLE ======================

       
        mysqli_query($con, "insert into order_booking(
        order_code, 
        order_num, 
        customer_code, 
        product_code,
        quantity,
        price,
        amount,
        shipping,
        shipping_charges,
        total_amount,
        status,
        payment_method,
        trans_id,
        trans_img,
        trans_date,
        booking_date,
        note,
        entry_user_code) values(
        '{$order_code} ',
        '{$order_num}',
        '{$customer_code}',
        '{$product_code}',
        '{$quantity}',
        '{$sale_price}',
        '{$amount} ',
        '{$shipping}',
        '{$shipping_charges}',
        '{$total_amount}',
        'Pending',
        '{$payment_method}',
        '{$trans_id}',
        '{$trans_img}',
        '{$trans_date}',
        '{$booking_date}',
        '{$note}',
        '{$customer_code}')");

        mysqli_query($con,"delete from customer_cart where cart_code='".$rw['cart_code']."' ");
        }
    // Return response
    if ($res) {
      $status =true; 
      $message = 'Data inserted successfully';
    } else {
      $status =false; 
      $message ='Data not inserted';
    }
    $response = [
        'status' => $status,
        'mssg' => $message,
    ];
    header("HTTP/1.0 200 Success");


    echo json_encode($response);
    
?>
