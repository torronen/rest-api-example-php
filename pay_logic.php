<?php

//define the amout to use in the test payment
$amount = 472;

if(isset($_GET['action']) && $_GET['action'] == 'check')
{
	try
	{
		// Checks the Paybyway interface if there is a payment with given token created and in which state is it
		$charge_result = $payment->checkStatusWithToken($_POST['token']);
		if($charge_result->result == 0)
		{
			/* 
			Handling if the payment was successful
			for example update order status in database
			*/
		}
		else
		{
			/*
			Handling if the payment was failed
			for example update order status in database
			*/
		}

		/*
		Echo result to client so client knows whether payment was successful
		*/
		echo json_encode($charge_result);
	}
	catch (Paybyway\PaybywayException $e) 
	{
		error_log($e->getMessage());
		header("HTTP/1.1 500 Internal Server Error", true, 500);
	}
	exit(0);
}
else if(isset($_GET['action']))
{
	//init lib with payment data
	
	//generate return url
	$return_url = strstr("http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], '?', true)."?return";

	$payment->addCharge(
			array(
			'order_number' => 'testpay_' . time(), 
			'amount' => $amount, 
			'currency' => 'EUR',
			'email' => 'testikauppias@paybyway.com'
			)
		);

		$payment->addCustomer(
			array(
				'firstname' => 'Teppo', 
				'lastname' => 'Testaaja', 
				'email' => 'teppo.testaaja@paybyway.com', 
				'address_street' => 'Testaddress 1',
				'address_city' => 'Testlandia',
				'address_zip' => '12345'
			)
		);

		$payment->addProduct(
			array(
				'id' => 'as123', 
				'title' => 'Product 1',
				'count' => 1,
				'pretax_price' => 300,
				'tax' => 24,
				'price' => 372,
				'type' => 1
			)
		);

		$payment->addProduct(
			array(
				'id' => 'ab1', 
				'title' => 'Shipping',
				'count' => 1,
				'pretax_price' => 100,
				'tax' => 0,
				'price' => 100,
				'type' => 2
			)
		);

	if($_GET['action'] == 'embedded_bank_payment')
	{
		//if selected payment method was embedded bank button, append lib with e-payment type payment data
		$payment->addPaymentMethod(
			array(
				'type' => 'e-payment', 
				'return_url' => $return_url,
				'notify_url' => $return_url,
				'lang' => 'fi',
				'token_valid_until' => strtotime('+2 hours'),
				'selected' => array($_POST['selected'])
			)
		);
		try
		{
			//create the charge request - throws an exception if something fails
			$response = $payment->createCharge();

			//if the payment was generated successfully, return the redirection address to frontend
			if($response->result == 0)
				echo json_encode(array("payment_url" => Paybyway\Paybyway::API_URL."/token/".$response->token));
		}
		catch (Paybyway\PaybywayException $e) 
		{
			// Error occured
			error_log($e->getMessage());
			header("HTTP/1.1 500 Internal Server Error", true, 500);
		}
		exit(0);
	}
	else if($_GET['action'] == 'pay_page_payment')
	{
		$payment->addPaymentMethod(
			array(
				'type' => 'e-payment', 
				'return_url' => $return_url,
				'notify_url' => $return_url,
				'lang' => 'fi',
				'token_valid_until' => strtotime('+2 hours')
			)
		);
		try
		{
			//create the charge request - throws an exception if something fails
			$response = $payment->createCharge();

			//if the payment was generated successfully, return the redirection address to frontend
			if($response->result == 0)
				echo json_encode(array("payment_url" => Paybyway\Paybyway::API_URL."/token/".$response->token));
		}
		catch (Paybyway\PaybywayException $e) 
		{
			// Error occured
			error_log($e->getMessage());
			header("HTTP/1.1 500 Internal Server Error", true, 500);
		}
		exit(0);
	}
	else if($_GET['action'] == 'credit_card_payment')
	{
		$payment->addPaymentMethod(
			array('type' => 'card', 'register_card_token' => 0)
		);
		try
		{
			// Return the response of the query back to the front, assuming to get a token of the created charge request
			$response = $payment->createCharge();
			if($response->result == 0)
				echo json_encode(array("token" => $response->token, "payment_url" => Paybyway\Paybyway::API_URL."/charge", "currency" => "EUR", "amount" => $amount));
		}
		catch (Paybyway\PaybywayException $e) 
		{
			// Error occured
			error_log($e->getMessage());
			header("HTTP/1.1 500 Internal Server Error", true, 500);
		}
		exit(0);
	}
	else
		exit(0);
}