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
    $this->fields['merchant_id'] = $fields['merchant_id'];

    if(isset($fields['email']))
      $this->fields['email'] = $fields['email'];

    $this->customer = null;
    $this->products = array();
  }

  public function setCustomer($customer)
  {
    $this->customer = $customer;
  }

  public function addProduct($product)
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
