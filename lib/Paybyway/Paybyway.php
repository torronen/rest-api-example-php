<?php

namespace Paybyway;

class Paybyway
{
  private $merchant_id;
  private $private_key;
  private $charge;
  private $connector;

  const API_URL = 'https://www.paybyway.com/pbwapi';
  const VERSION = 'w2';

  public function __construct($merchant_id, $private_key, PaybywayConnector $connector = null)
  {
    $this->merchant_id = $merchant_id;
    $this->private_key = $private_key;
    $this->connector = $connector ? $connector : new PaybywayCurl();
    $this->charge = null;
  }

  public function addCharge(array $fields)
  {
    $fields['merchant_id'] = $this->merchant_id;
    $this->charge = new PaybywayCharge($fields);
  }

  public function addCustomer(array $fields)
  {
    $this->charge->setCustomer(new PaybywayCustomer($fields));
  }

  public function addProduct(array $fields)
  {
    $this->charge->addProduct(new PaybywayProduct($fields));
  }

  public function createCharge()
  {
    $payment = $this->charge->toArray();
    
    $payment['version'] = self::VERSION;
    $payment['authcode'] = $this->calcAuthcode($this->merchant_id.'|'.$payment['amount'].'|'.$payment['currency']);

    $result = $this->connector->request("auth_payment", $payment);

    if($json = json_decode($result))
    {
      if(isset($json->result))
      {
        if($json->result == 0 && isset($json->token))
          return $json->token;
      }
    }

    throw new PaybywayException("Paybyway :: Connection error, cannot create payment", 2);
  }

  public function checkStatus($token)
  {
    $post_arr = array(
      'version' => self::VERSION,
      'authcode' => $this->calcAuthcode($this->merchant_id.'|'.$token),
      'token' => $token,
      'merchant_id' => $this->merchant_id
    );

    $result = $this->connector->request("check_payment_status", $post_arr);
    $json_result = json_decode($result);
    
    if ($json_result->result)
      return $json_result->result;
    else
      throw new PaybywayException("Paybyway :: Connection error, cannot verify payment", 2);
  }

  private function calcAuthcode($input)
  {
    return strtoupper(hash_hmac('sha256', $input, $this->private_key));
  }
}