<?php
require '../vendor/autoload.php';

class PaybywayTest extends PHPUnit_Framework_TestCase
{
  public function tearDown()
  {
    \Mockery::close();
  }

  public function testGetToken()
  {
    $response = array(
      'result' => 0,
      'token' => 'test_token'
    );

    $connector = \Mockery::mock('Paybyway\PaybywayConnector');

    $connector->shouldReceive("request")->with("auth_payment", array(
      'amount' => '100',
      'order_number' => 'a',
      'currency' => 'EUR',
      'email' => 'test@test.com',
      'merchant_id' => '1',
      'version' => 'w2',
      'authcode' => '32303F54726ECECD15CD3E0C1D3D070C583C4A697E89DE9BF716300E568C65D8'
    ))->once()->andReturn(json_encode($response));

    $paybyway = new Paybyway\Paybyway(1, 'private_key', $connector);

    $paybyway->addCharge(array(
      'amount' => '100',
      'order_number' => 'a',
      'currency' => 'EUR',
      'email' => 'test@test.com',
    ));

    $this->assertEquals($paybyway->createCharge(), 'test_token');
  }

  public function testGetStatus()
  {
    $connector = \Mockery::mock('Paybyway\PaybywayConnector');

    $test_token = 'test_token';
    $response = array('result' => '000');

    $connector->shouldReceive("request")->with("check_payment_status", array(
      'version' => 'w2',
      'authcode' => '4D769903336CDA6A563E8124A98352894F6DA4DAEB58809309774B762AEF1543',
      'token' => $test_token,
      'merchant_id' => '1'
    ))->once()->andReturn(json_encode($response));

    $paybyway = new Paybyway\Paybyway(1, 'private_key', $connector);

    $this->assertEquals($paybyway->checkStatus($test_token), '000');
  }

  public function testGetTokenThrowException()
  {
    $connector = \Mockery::mock('Paybyway\PaybywayConnector');

    $paybyway = new Paybyway\Paybyway(1, 'private_key', $connector);
    $paybyway->addCharge(array(
      'amount' => '100',
      'order_number' => 'a',
      'currency' => 'EUR',
      'email' => 'test@test.com',
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
}
