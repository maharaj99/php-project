<?php
include('../db.php');
include("../function/mlm-logic.php");

        header('content-type:application/json');
        header('Acess-control-Allow-Orifgin: *');
        header('Acess-control-Allow-Methods:post');
        header('Acess-control-Allow-Headers:Acess-control-Allow-Headers,content-type,Acess-control-Allow-Methods,Authorization,X-Requested-With');


        $data=json_decode(file_get_contents("php://input"),true);
        
        mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 0");


    
        
        if (empty($data['ph_num'])) {
            echo json_encode(['status' => false, 'mssg' => 'Missing phone number']);
            exit;
        } elseif (!is_numeric($data['ph_num'])) {
            echo json_encode(['status' => false, 'mssg' => 'Phone number should be numeric']);
            exit;
        } elseif (strlen($data['ph_num']) !== 10) {
            echo json_encode(['status' => false, 'mssg' => 'Phone number should be a 10-digit number']);
            exit;
        } else {
            $ph_num = $data['ph_num'];
        }

        if (empty( $data['customer_name'])) {
            echo json_encode(['status' => false, 'mssg' => 'Missing customer name']);   
            exit;
        }
        else {
            $customer_name = $data['customer_name'];
        }
        if (empty( $data['email_id'])) {
            echo json_encode(['status' => false, 'mssg' => 'Missing email id']);   
            exit;
        }
        else {
            $email_id = $data['email_id'];
        }
        if (empty( $data['password'])) {
            echo json_encode(['status' => false, 'mssg' => 'Missing password']);   
            exit;
        }
        else {
            $password =$data['password'];
        }
        if (empty( $data['dob'])) {
            echo json_encode(['status' => false, 'mssg' => 'Missing DOB']);   
            exit;
        }
        else {
            $dob = $data['dob'];
        }


        $mr_mrs =$data['mr_mrs'];
        $sponsor_id =$data['sponsor_id'];
        $state = $data['state'];
        $district =$data['district'];
        $city = $data['city'];
        $pincode =$data['pincode'];
        $address = $data['address'];
        $active = 'Yes';


        $sponsor_customer_dataget = mysqli_query($con, "select customer_code from customer_master where ref_id='" . $sponsor_id . "' and active='Yes' ");
        $sponsor_customer_data = mysqli_fetch_row($sponsor_customer_dataget);
        $ref_customer_code = $sponsor_customer_data[0];

        $user_id = $ph_num;
        $encodePassword = base64_encode($password);

        
        // CHECK ph_num 
        $dataget = mysqli_query($con, "SELECT COUNT(*) FROM customer_master WHERE ph_num='" . $ph_num . "'");
        $data = mysqli_fetch_row($dataget);
        if ($data[0] > 0) {
            echo json_encode(['status' => false, 'mssg' => 'Phone Number Already Exists']);
            exit;
        }

 


        //========= FIRST MAIN CUSTOMER DATAGET ==============//
        $customer_dataget = mysqli_query($con, "select customer_code from customer_master where main='Yes' ");
        $customer_data = mysqli_fetch_row($customer_dataget);

        if ($customer_data) {
            $main = 'No';
            $main_customer_code = $customer_data[0];
        } else {
            $main = 'Yes';
            $main_customer_code = '';
        }
            if ($main_customer_code != "") {
                $customerFree = customerUnderFreeCheck("'" . $main_customer_code . "'");
                if ($customerFree == "No") {
                    echo json_encode(['status' => false, 'mssg' => 'Ref Customer 8 Level Full Fill !!']);

                }
            }

      
            $customer_code = "CUC_" . uniqid() . time();
 
    
            $sql="insert into customer_master ( 
                customer_code, 
                mr_mrs,
                customer_name, 
                ph_num, 
                email_id, 
                user_id, 
                password,
                dob,
                state,
                district,
                city,
                pincode,
                address,
                active,
                ref_customer_code,
                main) values(
                    '{$customer_code}','{$mr_mrs}','{$customer_name}','{$ph_num}','{$email_id}','{$user_id}','{$encodePassword}','{$dob}','{$state}','{$district}','{$city}','{$pincode}','{$address}','{$active}','{$ref_customer_code}','{$main}')";

                $res=mysqli_query($con,$sql);



                $ref_user_data="";
                if ($main_customer_code != "") {

                $ref_user_dataget = mysqli_query($con, "select count(*) from customer_master where under_customer_code='" . $main_customer_code . "' ");
                $ref_user_data = mysqli_fetch_row($ref_user_dataget);

                if ($ref_user_data[0] < 3) {

                    mysqli_query($con, "update customer_master set under_customer_code='{$main_customer_code}', level='1',level_num='" . ($ref_user_data[0] + 1) . "' where customer_code='{$customer_code}'");
                } else {
                    checkUserUnderAvailability("'" . $main_customer_code . "'", $customer_code, 2);
                }
                
                
            }

            if ($res) {
                echo json_encode(['status' => true, 'mssg' => 'Register Successfully']);
            } else {
                echo json_encode(['status' => false, 'mssg' => 'data not insert ']);
            }

       
?>

