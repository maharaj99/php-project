<?php
include('../db.php');
include("../function/mlm-logic.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Main program
$n = 6;

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

// CHECK ph_num 
$dataget = mysqli_query($con, "select * from customer_master where ph_num='" . $ph_num . "'");
$dataExists = mysqli_fetch_row($dataget);

if ($dataExists) {
    echo json_encode(['status' => false, 'mssg' => 'Already Exist Same Phone Number !!']);
    exit;
} else {
    // Generate otp
    $generator = "123456789";
    $result = "";

    for ($i = 1; $i <= $n; $i++) {
        $result .= substr($generator, rand() % strlen($generator), 1);
    }

    if ($result != "") {


        function curl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        $username = "cbuy2020"; //your username
        $password = "cbuy2020"; //your password
        $sender = "MYCBUY"; //Your senderid
        $mobile = $ph_num; //enter Mobile numbers comma seperated
        $username = urlencode($username);
        $password = urlencode($password);
        $messagecontent = "One- time password for your mobile no. verification is ".$result." -CBUY "; //Type Of Your Message
        $message = urlencode($messagecontent);
        $route = "T"; //your route id
        $peid = "1701163438699646384"; //your 19-digit Entity ID
        $tempid = "1707170478858142086"; //your 19-digit Template ID
        $url = "http://sms.firstdial.info/sendsms?uname=$username&pwd=$password&senderid=$sender&to=$mobile&msg=$message&route=$route&peid=$peid&tempid=$tempid";
        $response = curl($url);

        $status = 'Success';
        $responseData = [
            'phone_number' => $ph_num,
            'otp' => ''
        ];
        $message = 'OTP generated successfully';

        mysqli_query($con, "delete from otp_master where phone_num='" . $ph_num . "' ");

        //insert otp
        $otp_code = "OC_" . uniqid() . time();
        $sql = "insert into otp_master (otp_code,otp,phone_num) values(
                '{$otp_code}','{$result}','{$ph_num}')";

        $res = mysqli_query($con, $sql);
    } else {
        $status = 'Failed';
        $message = 'OTP generation failed';
    }
}

$response = [
    'status' => $status,
    'data' => $responseData,
    'mssg' => $message,
];

header("HTTP/1.0 200 Success");

echo json_encode($response);
