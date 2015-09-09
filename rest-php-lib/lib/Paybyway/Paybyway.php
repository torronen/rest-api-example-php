<?php

namespace Paybyway;

class Paybyway
{
	private $api_key;
	private $private_key;
	private $charge;
	private $connector;
	private $version;

	const API_URL = 'https://www.paybyway.com/pbwapi';

	public function __construct($api_key, $private_key, $version = 'w2.1', PaybywayConnector $connector = null)
	{
		$this->api_key = $api_key;
		$this->private_key = $private_key;
		$this->connector = $connector ? $connector : new PaybywayCurl();
		$this->version = $version;
		$this->charge = null;
	}

	public function addCharge(array $fields)
	{
		$fields['api_key'] = $this->api_key;
		$this->charge = new PaybywayCharge($fields);
	}	

	public function addCustomer(array $fields)
	{
		$this->charge->setCustomer(new PaybywayCustomer($fields));
	}

	public function addProduct(array $fields)
	{
		$this->charge->setProduct(new PaybywayProduct($fields));
	}

	public function createCharge()
	{
		$payment = $this->charge->toArray();
		
		$payment['version'] = $this->version;
		$payment['authcode'] = $this->calcAuthcode($this->api_key.'|'.$payment['amount'].'|'.$payment['currency']);

		$result = $this->connector->request("auth_payment", $payment);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: createCharge - response not valid JSON", 2);
	}

	public function chargeWithCardToken()
	{
		$payment = $this->charge->toArray();

		$payment['version'] = $this->version;
		$payment['authcode'] = $this->calcAuthcode($this->api_key.'|'.$payment['amount'].'|'.$payment['currency'].'|'.$payment['card_token']);

		$result = $this->connector->request("charge_card_token", $payment);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: chargeWithCardToken - response not valid JSON", 2);
	}

	public function checkStatus($token)
	{
		$post_arr = array(
			'version' => $this->version,
			'authcode' => $this->calcAuthcode($this->api_key.'|'.$token),
			'token' => $token,
			'api_key' => $this->api_key
			);

		$result = $this->connector->request("check_payment_status", $post_arr);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: checkStatus - response not valid JSON", 2);
	}

	public function settlePayment($order_number)
	{
		$post_arr = array(
			'version' => $this->version,
			'authcode' => $this->calcAuthcode($this->api_key.'|'.$order_number),
			'order_number' => $order_number,
			'api_key' => $this->api_key
			);

		$result = $this->connector->request("capture", $post_arr);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: settlePayment - response not valid JSON", 2);	
	}

	public function cancelPayment($order_number)
	{
		$post_arr = array(
			'version' => $this->version,
			'authcode' => $this->calcAuthcode($this->api_key.'|'.$order_number),
			'order_number' => $order_number,
			'api_key' => $this->api_key
			);

		$result = $this->connector->request("cancel", $post_arr);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: cancelPayment - response not valid JSON", 2);	
	}

	public function getCardToken($card_token)
	{
		$post_arr = array(
			'version' => $this->version,
			'authcode' => $this->calcAuthcode($this->api_key.'|'.$card_token),
			'card_token' => $card_token,
			'api_key' => $this->api_key
			);

		$result = $this->connector->request("get_card_token", $post_arr);

		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: getCardToken - response not valid JSON", 2);
	}

	public function deleteCardToken($card_token)
	{
		$post_arr = array(
			'version' => $this->version,
			'authcode' => $this->calcAuthcode($this->api_key.'|'.$card_token),
			'card_token' => $card_token,
			'api_key' => $this->api_key
			);

		$result = $this->connector->request("delete_card_token", $post_arr);
		
		if($json = json_decode($result))
		{
			if(isset($json->result))
				return $json;
		}

		throw new PaybywayException("Paybyway :: getCardToken - response not valid JSON", 2);	
	}

	private function calcAuthcode($input)
	{
		return strtoupper(hash_hmac('sha256', $input, $this->private_key));
	}
}