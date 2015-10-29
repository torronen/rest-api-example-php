<?php

if(isset($_GET['return']))
{
	try
	{
		$return = $payment->checkReturn($_GET);
		if($return->RETURN_CODE == 0)
		{
			$payment_return = 0;
		}
		else
		{
			if(isset($return->INCIDENT_ID))
				error_log("Payment error, incident_id: ".$return->INCIDENT_ID);

			$payment_return = 1;
		}
	}
	catch (Paybyway\PaybywayException $e) 
	{
		error_log($e->getMessage());
		$payment_return = 2;
		header("HTTP/1.1 500 Internal Server Error", true, 500);
	}
}