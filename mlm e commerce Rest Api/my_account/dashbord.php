<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

include('../db.php');

// GET POST DATA BY FORM POST
$postData = json_decode(file_get_contents("php://input"), true);

// get the auth_token
function get_headers_compat()
{
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
require_once '../function/customer_balance.php';

// Query 1
$customer_dataget = mysqli_query($con, "select 
                            customer_name,
                            ref_id,
                            customer_img,
                            cust_type,
                            level,
                            level_num,
                            rank
                            from customer_master where customer_code='$customer_code' ");
$customer_data = mysqli_fetch_assoc($customer_dataget);


// Query 2
$max_current_level_dataget = mysqli_query($con, "select max(level) from customer_master where active='Yes' ");
$max_current_level = mysqli_fetch_row($max_current_level_dataget)[0];

// Query 3
$max_current_level_num_dataget = mysqli_query($con, "select max(level_num) from customer_master where active='Yes' and level='" . $max_current_level . "' ");
$max_current_level_num = mysqli_fetch_row($max_current_level_num_dataget)[0];

// Query 4
$in_dataget = mysqli_query($con, "select sum(bv) from customer_trans where status='In' and type='BV' ");
$in_data = mysqli_fetch_row($in_dataget);

// Query 5
$out_dataget = mysqli_query($con, "select sum(bv) from customer_trans where status='Out' and type='BV' ");
$out_data = mysqli_fetch_row($out_dataget);

// Query 6
$dataget = mysqli_query($con, "select sum(total_pv) from voucher_master where customer_code='" . $customer_code . "'");
$total_pv_data = mysqli_fetch_row($dataget);

//get wallet balence

$walletBalance = getWalletBalance($customer_code);

//get direct commisssion

$DirectCommisssion = getTypeBalance($customer_code, 'Direct Commission');

//get BDC

$BDC = getTypeBalance($customer_code, 'Business Development Commission');

//get TDC

$TDC = getTypeBalance($customer_code, 'Team Development Commission');

//get TB

$TB = getTypeBalance($customer_code, 'Team Bonus');

//get RI

$RI = getTypeBalance($customer_code, 'Referral Income');

//BDE

$BDE = getTypeBalance($customer_code, 'Business Development Expence');

// BV

$in_dataget = mysqli_query($con, "select sum(bv) from customer_trans where status='In' and type='BV' ");
$in_data = mysqli_fetch_row($in_dataget);
$total_in_bv = $in_data[0];

$out_dataget = mysqli_query($con, "select sum(bv) from customer_trans where status='Out' and type='BV' ");
$out_data = mysqli_fetch_row($out_dataget);
$total_out_bv = $out_data[0];

$total_BV = $total_in_bv - $total_out_bv;

//BV Comission 
$BV_Comission = getTypeBalance($customer_code, 'BV Commission');

//Referral Bonus

$Referral_Bonus = getTypeBalance($customer_code, 'Referral Bonus');

//Payout

$Payout = getTotalPayout($customer_code);

//Total Amount

$Total_Amount = getTotalPayout($customer_code) + getWalletBalance($customer_code);

//Self EV 

$datagetself_Ev = mysqli_query($con, "select sum(total_ev) from voucher_master where customer_code='" . $customer_code . "' ");
$self_ev = mysqli_fetch_row($datagetself_Ev);
$ev = $self_ev[0];


// 3% BV
$three_percentage_dataget = mysqli_query($con, "select sum(bv) from customer_trans where customer_code='" . $customer_code . "' and type='BV Commission' and ( bv_percentage='3%' or bv_percentage='' ) ");
$three_parcentage_data = mysqli_fetch_row($three_percentage_dataget);

// 5% BV
$five_percentage_bv_dataget = mysqli_query($con, "select sum(bv) from customer_trans where customer_code='" . $customer_code . "' and type='BV Commission' and bv_percentage='5%' ");
$five_percentage_data = mysqli_fetch_row($five_percentage_bv_dataget);



// Constructing the response array
$response = [
    'status' => 'Success',
    'message' => 'Data Fetched Successfully',
    'customer_name' => $customer_data['customer_name'],
    'ref_id' => $customer_data['ref_id'],
    'customer_img' => $customer_data['customer_img'],
    'cust_type' => $customer_data['cust_type'],
    'level' => $customer_data['level'],
    'level_num' => $customer_data['level_num'],
    'rank' => $customer_data['rank'],    'max_current_level' => $max_current_level,
    'max_current_level_num' => $max_current_level_num,
    'in_data' => $in_data[0],
    'out_data' => $out_data[0],
    'total_pv_data' => $total_pv_data[0],
    'walletBalance' => $walletBalance,
    'DirectCommisssion' => $DirectCommisssion,
    'BDC' => $BDC,
    'BDE' => $BDE,
    'TDC' => $TDC,
    'TB' => $TB,
    'RI' => $RI,
    'BV' => $total_BV,
    'BV_Comission' => $BV_Comission,
    'Referral_Bonus' => $Referral_Bonus,
    'Payout' => $Payout,
    'Total_Amount' => $Total_Amount,
    'self_ev' => $ev,
    'per_3_bv' => $three_parcentage_data[0],
    'per_5_bv' => $five_percentage_data[0],
];

header("HTTP/1.0 200 Success");
echo json_encode($response);
