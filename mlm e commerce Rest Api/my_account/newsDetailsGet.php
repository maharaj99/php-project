<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header("Access-Control-Allow-Methods: POST");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

    include('../db.php');

    // GET POST DATA BY FORM POST
    $postData = json_decode(file_get_contents("php://input"), true);

    // //get the auth_token
    // function get_headers_compat() {
    //     if (!function_exists('getallheaders')) {
    //         $headers = [];
    //         foreach ($_SERVER as $name => $value) {
    //             if (substr($name, 0, 5) == 'HTTP_') {
    //                 $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    //             }
    //         }
    //         return $headers;
    //     } else {
    //         return getallheaders();
    //     }
    // }
 
    // $headers = get_headers_compat();

    // $auth_token = isset($headers['Auth-Token']) ? $headers['Auth-Token'] : null;

    // $auth_token = mysqli_real_escape_string($con, $auth_token);

    // //send the auth_token 
    // require_once '../function/verify_authToken.php';
                            
    $news_dataget = mysqli_query($con, "select * from news_master order by publish_date_time DESC ");

    $news_data=[];

    while ($row = mysqli_fetch_assoc($news_dataget)) {
        $news_data[] = $row;
    }

    if ($news_data) {
        $status = "Success";
        $message = "News Data Fetched Successfully";
        $SendData = $news_data;
    } else {
        $status = "Not Found";
        $message = "Data Not Found";
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
