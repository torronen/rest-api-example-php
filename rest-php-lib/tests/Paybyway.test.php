<?php
require '../vendor/autoload.php';

class PaybywayTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		\Mockery::close();
	}

	public function testGetTokenCard()
	{
		$response = array(
			'result' => 0,
			'token' => 'test_token',
			'type' => 'card'
			);

		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$connector->shouldReceive("request")->with("auth_payment", array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR',
			'api_key' => 'TESTAPIKEY',
			'version' => 'w3',
			'authcode' => '23746DEF83C7EC93A1A6D9E03E569032804535230DC3DEDDF575699456C63BFF',
			'payment_method' => array('type' => 'card')
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$paybyway->addCharge(array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR'
			));
		$paybyway->addPaymentMethod(array(
				'type' => 'card',
			));

		$request = $paybyway->createCharge();

		$this->assertEquals($request->token, 'test_token');
		$this->assertEquals($request->type, 'card');
	}


	public function testGetTokenEPayment()
	{
		$response = array(
			'result' => 0,
			'token' => 'test_token',
			'type' => 'e-payment'
			);

		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$connector->shouldReceive("request")->with("auth_payment", array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR',
			'api_key' => 'TESTAPIKEY',
			'version' => 'w3',
			'authcode' => '23746DEF83C7EC93A1A6D9E03E569032804535230DC3DEDDF575699456C63BFF',
			'payment_method' => array('type' => 'e-payment', 'return_url' => 'https://localhost/return', 'notify_url' => 'https://localhost/return')
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$paybyway->addCharge(array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR'
			));
		$paybyway->addPaymentMethod(array(
				'type' => 'e-payment', 
				'return_url' => 'https://localhost/return',
				'notify_url' => 'https://localhost/return'
			));

		$request = $paybyway->createCharge();

		$this->assertEquals($request->token, 'test_token');
		$this->assertEquals($request->type, 'e-payment');
	}

	public function testGetStatus()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$test_token = 'test_token';
		$response = array('result' => 0);
		$response = json_encode($response);

		$connector->shouldReceive("request")->with("check_payment_status", array(
			'version' => 'w3',
			'authcode' => 'B33468AA646E3835C40929AA3361A44F4923E95D90F84CB14CDF9C70507E4384',
			'token' => $test_token,
			'api_key' => 'TESTAPIKEY'
			))->once()->andReturn($response);

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$request = $paybyway->checkStatus($test_token);

		$this->assertEquals($request->result, 0);
	}

	public function testGetTokenThrowsException()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);
		$paybyway->addCharge(array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR'
			));

		$connector->shouldReceive("request")->once()->andReturn('kkk');

		try 
		{
			$paybyway->createCharge();
		}
		catch(Paybyway\PaybywayException $e)
		{

		}

		$this->assertTrue(true);
	}

	public function testChargeCardToken()
	{
		$response = array(
			'result' => 0,
			'settled' => 1,
			'source' => array(
				'object' => 'card',
				'card_token' => 'card_token'
				//Should return card info here but not relevant
				)
			);

		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$connector->shouldReceive("request")->with("charge_card_token", array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR',
			'api_key' => 'TESTAPIKEY',
			'version' => 'w3',
			'card_token' => 'card_token',
			'authcode' => 'AD8F8D0FB039C5685D54E9EC1DEEA2972C00C46F2ED4B260481BD225209C0982'
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$paybyway->addCharge(array(
			'amount' => '100',
			'order_number' => 'a',
			'currency' => 'EUR',
			'card_token' => 'card_token'
			));

		$request = $paybyway->chargeWithCardToken();

		$this->assertEquals($request->result, 0);
	}

	public function testCapture()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$response = array('result' => 0);

		$connector->shouldReceive("request")->with("capture", array(
			'version' => 'w3',
			'authcode' => '23746DEF83C7EC93A1A6D9E03E569032804535230DC3DEDDF575699456C63BFF',
			'order_number' => 'a',
			'api_key' => 'TESTAPIKEY'
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$request = $paybyway->settlePayment('a');

		$this->assertEquals($request->result, 0);
	}

	public function testCancel()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$response = array('result' => 0);

		$connector->shouldReceive("request")->with("cancel", array(
			'version' => 'w3',
			'authcode' => '23746DEF83C7EC93A1A6D9E03E569032804535230DC3DEDDF575699456C63BFF',
			'order_number' => 'a',
			'api_key' => 'TESTAPIKEY'
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$request = $paybyway->cancelPayment('a');

		$this->assertEquals($request->result, 0);
	}

	public function testGetCardToken()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$response = array(
			'result' => 0,
			'source' => array(
				'object' => 'card',
				'last4' => '1234',
				'brand' => 'Visa',
				'exp_year' => 2015,
				'exp_month' => 5,
				'card_token' => 'card_token'
				)
			);

		$connector->shouldReceive("request")->with("get_card_token", array(
			'version' => 'w3',
			'authcode' => 'A2080435816D3C7C893E246B3651F12F80131984617468B0426DC6D9DD9ED0ED',
			'card_token' => 'card_token',
			'api_key' => 'TESTAPIKEY'
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$request = $paybyway->getCardToken('card_token');
		//returns object, response here is array
		$this->assertEquals(json_encode($request), json_encode($response));
	}

	public function testDeleteCardToken()
	{
		$connector = \Mockery::mock('Paybyway\PaybywayConnector');

		$response = array('result' => 0);
  
		$connector->shouldReceive("request")->with("delete_card_token", array(
			'version' => 'w3',
			'authcode' => 'A2080435816D3C7C893E246B3651F12F80131984617468B0426DC6D9DD9ED0ED',
			'card_token' => 'card_token',
			'api_key' => 'TESTAPIKEY'
			))->once()->andReturn(json_encode($response));

		$paybyway = new Paybyway\Paybyway('TESTAPIKEY', 'private_key', 'w3', $connector);

		$request = $paybyway->deleteCardToken('card_token');

		$this->assertEquals($request->result, 0);
	}
}
