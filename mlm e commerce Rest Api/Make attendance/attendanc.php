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

    $attendance_code = isset($postData['attendance_code']) ? $postData['attendance_code'] : null;

    if (empty($attendance_code)) {
        echo json_encode(['status' => false, 'field' => 'attendance_code', 'mssg' => 'attendance code is blank']);
        exit;
    }

    // Fetch data from the database based on the attendance code
    $query = "SELECT * FROM attendance_timing WHERE attendance_timing_code = :attendance_code";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':attendance_code', $attendance_code);
    $stmt->execute();
    $attendance_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attendance_data) {
        echo json_encode(['status' => false, 'mssg' => 'Invalid attendance code']);
        exit;
    }

    $current_time = time();
    $otp_validate_time = strtotime($attendance_data['otp_validate_time']);

    if ($current_time > $otp_validate_time) {
        echo json_encode(['status' => false, 'mssg' => 'OTP validation time has expired']);
        exit;
    }

    // Continue with your logic if the attendance code is valid and within the OTP validation time

    $response = [
        'status' => true,
        'mssg' => 'Attendance code is valid',
        'data' => $your_data, // Include your data here
    ];

    header("HTTP/1.0 200 Success");
    echo json_encode($response);
?>
