<?php
include("../db/db.php");

$status = "";
$status_text = "";
$paymentUrl = "";

if ($login == "No") {
	$status = "SessionDestroy";
	$status_text = "";
} else {

	$sendData = json_decode($_POST['sendData'], true);

	$customer_code = $session_user_code;
	$first_name = mysqli_real_escape_string($con, $sendData['first_name']);
	$last_name = mysqli_real_escape_string($con, $sendData['last_name']);
	$company_name = mysqli_real_escape_string($con, $sendData['company_name']);
	$country = mysqli_real_escape_string($con, $sendData['country']);
	$street_address_1 = mysqli_real_escape_string($con, $sendData['street_address_1']);
	$ststreet_address_2ate = mysqli_real_escape_string($con, $sendData['street_address_2']);
	$town = mysqli_real_escape_string($con, $sendData['town']);
	$zip = mysqli_real_escape_string($con, $sendData['zip']);
	$state = mysqli_real_escape_string($con, $sendData['state']);
	$ph_num = mysqli_real_escape_string($con, $sendData['ph_num']);
	$email = mysqli_real_escape_string($con, $sendData['email']);
	$note = mysqli_real_escape_string($con, $sendData['note']);
	$shipping = mysqli_real_escape_string($con, $sendData['shipping']);
	$payment_method = mysqli_real_escape_string($con, $sendData['payment_method']);

	$execute = 1;

	if ($execute == 1) {

		mysqli_query($con, "delete from customer_address where customer_code='" . $customer_code . "' ");

		$address_code = "CAC_" . uniqid() . time();
		// INSERT IN TABLE
		mysqli_query($con, "insert into customer_address ( 
					address_code, 
                    customer_code, 
                    first_name, 
                    last_name, 
                    company_name, 
                    country,
                    street_address_1,
                    street_address_2,
                    town,
                    zip,
                    state,
                    ph_num,
                    email,
                    entry_user_code) values(
					'" . $address_code . "',
					'" . $customer_code	 . "',
					'" . $first_name . "',
					'" . $last_name . "',
					'" . $company_name . "',
					'" . $country . "',
					'" . $street_address_1 . "',
					'" . $street_address_2 . "',
					'" . $town . "',
					'" . $zip . "',
					'" . $state . "',
					'" . $ph_num . "',
					'" . $email . "',
                    '" . $session_user_code . "')");

		$cart_dataget = mysqli_query($con, "select 
					customer_cart.cart_code,
					customer_cart.product_code,
					customer_cart.quantity,
					product_master.product_name,
					product_master.sale_price,
					product_master.courier_charges
					from customer_cart
					LEFT JOIN product_master ON product_master.product_code = customer_cart.product_code
					where customer_cart.customer_code='" . $session_user_code . "' and purchase='Yes' order by customer_cart.entry_timestamp DESC ");

		$totalPurchaseAmount = 0;

		while ($rw = mysqli_fetch_array($cart_dataget)) {

			$amount = $rw['sale_price'] * $rw['quantity'];

			if ($shipping == "Yes") {
				$shipping_charges = $rw['courier_charges'];
				$total_amount = $amount + $shipping_charges;
			} else {
				$shipping_charges = 0;
				$total_amount = $amount;
			}

			mysqli_query($con,"update customer_cart set shipping='".$shipping."', note='".$note."' where cart_code='".$cart_code."' ");

			$totalPurchaseAmount += $total_amount;
		}

		if ($totalPurchaseAmount > 0) {

			$key = $paymentGatewayKey;	// Your Api Token https://merchant.upigateway.com/user/api_credentials (assign in db.php)
			$post_data = new stdClass();
			$post_data->key = $key;
			$post_data->client_txn_id = (string) rand(100000, 999999); // you can use this field to store order id;
			$post_data->amount = (string) $totalPurchaseAmount;
			$post_data->p_info = "product_name";
			$post_data->customer_name = 'Customer Name';
			$post_data->customer_email = 'adsdsa@adas.asd';
			$post_data->customer_mobile = '9874563210';
			$post_data->redirect_url = $redirectUrl; // automatically ?client_txn_id=xxxxxx&txn_id=xxxxx will be added on redirect_url (assign in db.php)

			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => 'https://api.ekqr.in/api/create_order',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($post_data),
				CURLOPT_HTTPHEADER => [
					"Accept: */*",
					'Content-Type: application/json'
				],
			]);

			if (($res = curl_exec($curl)) === false) {
				$status = "error";
				$status_text = "Some Problem Happend In Payment. Please try after some time";
			}
			curl_close($curl);

			$result = json_decode($res, true);

			if ($result['status'] == true) {
				$status = "success";
				$status_text = "Payment Processing Done";
				$paymentUrl = $result['data']['payment_url'];
			}
		} else {
			$status = "error";
			$status_text = "Some Problem Happend In Payment. Please try after some time";
		}
	}
}

$response[] = [
	'status' => $status,
	'status_text' => $status_text,
	'paymentUrl' => $paymentUrl,
];
echo json_encode($response, true);
