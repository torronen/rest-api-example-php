<?php

namespace Paybyway;

class PaybywayCharge extends Paybyway
{
  private $fields;
  private $customer;
  private $products;

  public function __construct(array $fields)
  {
    $this->fields['amount'] = $fields['amount'];
    $this->fields['order_number'] = $fields['order_number'];
    $this->fields['currency'] = $fields['currency'];
    $this->fields['api_key'] = $fields['api_key'];

    if(isset($fields['register_card_token']))
    	$this->fields['register_card_token'] = $fields['register_card_token'];
    
    if(isset($fields['card_token']))
    	$this->fields['card_token'] = $fields['card_token'];

    $this->customer = null;
    $this->products = array();
  }

  public function setCustomer($customer)
  {
    $this->customer = $customer;
  }

  public function setProduct($product)
  {
    $this->products[] = $product;
  }

  public function toArray()
  {
    $array = (array)$this->fields;

    if($this->customer)
      $array['customer'] = $this->customer->getCustomerInfo();
    
    foreach($this->products as $product)
    {
      $array['products'][] = $product->getProductDetails();
    }
    return $array;
  }
}
