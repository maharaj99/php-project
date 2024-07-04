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
    

    if (empty($_FILES["aadhar_card"]["name"])) {
        echo json_encode(['status' => false, 'mssg' => 'Please upload a aadhar']);
        exit;
    }
    if (empty($_FILES["voter_card"]["name"])) {
        echo json_encode(['status' => false, 'mssg' => 'Please upload a voter']);
        exit;
    }
    if (empty($_FILES["kyc_photo"]["name"])) {
        echo json_encode(['status' => false, 'mssg' => 'Please upload a kyc photo']);
        exit;
    }

    
    $kyc_code = "CKC_" . uniqid() . time();
    $status1 = "Pending";
    $note = isset($_POST['note']) ? mysqli_real_escape_string($con, $_POST['note']) : '';


   //images upload
    $target_dir = "../../upload_content/upload_img/customer_kyc/";




    // Get the adhar card uploaded file information
    $aadhar_card_file_name = "aadhar_card_" . date("d-m-Y-H-i-s") . "-" . time();
    $aadhar_card_fl = $_FILES['aadhar_card']['name'];
    $original_file_name = $_FILES['aadhar_card']['name'];
    $ext = pathinfo($original_file_name, PATHINFO_EXTENSION);

    if (in_array($ext, $allowedFileExt)) {

        // Generate a unique file name
        $aadhar_card = $aadhar_card_file_name . "." . $ext;

        $target_file = $target_dir . $aadhar_card;

        // Move the uploaded file to the target directory
        move_uploaded_file($_FILES["aadhar_card"]["tmp_name"], $target_file);       

        $status = "Success";
        $message =" Aadhar card Uploaded Successfully";    
    }
    else{
        $status = "File Type Error";
        $message = "This File Type Not Acceptable";        
        exit;
    }

        // Get the voter card uploaded file information
        $voter_card_file_name = "voter_card" . date("d-m-Y-H-i-s") . "-" . time();
        $voter_card_fl = $_FILES['voter_card']['name'];
        $original_file_name = $_FILES['voter_card']['name'];
        $ext = pathinfo($original_file_name, PATHINFO_EXTENSION);
    
        if (in_array($ext, $allowedFileExt)) {
    
            // Generate a unique file name
            $voter_card = $voter_card_file_name . "." . $ext;
    
            $target_file = $target_dir . $voter_card;
    
            // Move the uploaded file to the target directory
            move_uploaded_file($_FILES["voter_card"]["tmp_name"], $target_file);       
    
            $status = "Success";
            $message =" Voter card Uploaded Successfully";    
        }
        else{
            $status = "File Type Error";
            $message = "This File Type Not Acceptable";        
            exit;
        }

       // Get the voter card uploaded file information
       $kyc_photo_file_name = "kyc_photo" . date("d-m-Y-H-i-s") . "-" . time();
       $kyc_photo_fl = $_FILES['kyc_photo']['name'];
       $original_file_name = $_FILES['kyc_photo']['name'];
       $ext = pathinfo($original_file_name, PATHINFO_EXTENSION);
   
       if (in_array($ext, $allowedImgExt)) {
   
           // Generate a unique file name
           $kyc_photo = $kyc_photo_file_name . "." . $ext;
   
           $target_file = $target_dir . $kyc_photo;
   
           // Move the uploaded file to the target directory
           move_uploaded_file($_FILES["kyc_photo"]["tmp_name"], $target_file);       
   
           $status = "Success";
           $message ="kyc photo Uploaded Successfully";    
       }
       else{
           $status = "File Type Error";
           $message = "This File Type Not Acceptable";        
           exit;
       }

    mysqli_query($con, "delete from customer_kyc where customer_code='" . $customer_code . "' ");

    $res = mysqli_query($con, "INSERT INTO customer_kyc ( 
        kyc_code, 
        customer_code, 
        aadhar_card,
        voter_card,
        kyc_photo,
        status,
        note,
        entry_user_code) VALUES (
            '{$kyc_code}',
            '{$customer_code}',
            '{$aadhar_card}',
            '{$voter_card}',
            '{$kyc_photo}',
            '{$status1}',
            '{$note}',
            '{$customer_code}')");

    // ... (previous code)

    // Handle errors and return response
    if ($res) {
        $status = true;
        $message = 'Data inserted successfully';
    } else {
        $status = false;
        $message = 'Data not inserted';
    }

    $response = [
        'status' => $status,
        'mssg' => $message,
    ];

    // Set the appropriate HTTP response code
    http_response_code($status ? 200 : 500);

    echo json_encode($response);

    
?>