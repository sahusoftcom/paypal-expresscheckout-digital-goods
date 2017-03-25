## PayPal ExpressCheckout Digital Goods Laravel Version: 1.0

Service Provider of PayPal.ExpressCheckout.Digital.Goods API for Laravel PHP Framework 
[Packagist]: <https://packagist.org/packages/sahusoftcom/paypal-expresscheckout-digital-goods>

## Installation:

Type the following command in your project directory
`composer require sahusoftcom/paypal-expresscheckout-digital-goods`

OR

Add the following line to the `require` section of `composer.json`:
```json
{
    "require": {
        "sahusoftcom/paypal-expresscheckout-digital-goods": "dev-master"
    }
}
```

## Setup:

In `/config/app.php`, add the following to `aliases`:
  
```php
'PayPal' => SahusoftCom\PayPal::class,
```

## How to use:

* You should use the `PayPal` class to access its function.
* Then follow the below example for further assisstance, 

```php
<?php
namespace App;
 
use PayPal\PaymentRequest;
use PayPal\PaymentResponse;

class PaymentController {	

    	public $apiContext;
    
	public function __construct()
	{
	    $apiContext = (object)[];
		
            $apiContext->APIUSERNAME = "YOUR-API-USERNAME";
            $apiContext->APIPASSWORD = "YOUR-API-PASSWORD";
            $apiContext->APISIGNATURE = "YOUR-API-SIGNATURE";
            $apiContext->ENDPOINT = "https://api-3t.sandbox.paypal.com/nvp";
            $apiContext->VERSION = "65.1";
            $apiContext->REDIRECTURL = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
	    
	    $data = [];
            $data['RETURNURL'] = "http://paypal.local.geekydev.com/getDone";
            $data['CANCELURL'] = "http://paypal.local.geekydev.com/getCancel";
        
            $data['CURRENCY'] = "USD";
            $data['TOTAL_AMOUNT'] = "100";
            $data['AMOUNT'] = "100";
            $data['TAX_AMOUNT'] = "0";
            $data['DESCRIPTION'] = "Movies";
            $data['PAYMENT_ACTION'] = "SALE";
            $data['NOSHIPPING'] = "1```";
        
            $data['ITEM_LIST'] = [];
            $data['ITEM_LIST'][0] = [
            	'NAME'			=> 'First Item Name',
        		'NUMBER'		=> 123,
        		'QUANTITY'		=> 1,
        		'TAX_AMOUNT'	=> 0,
        		'AMOUNT'		=> 100,
        		'URL'           => "Your product's url",
        		'DESCRIPTION'	=> 'First Name Description'
            ];
	}
	
	public function checkOut()
	{
            $object = new PaymentRequest($this->apiContext);
            $object->execute($this->data);
	}
	
	public function getDone()
	{
            $object = new \SahusoftCom\PayPal\PaymentResponse($apiContext);
            $response = $object->handle($this->data);

            echo "<pre>";
            print_r($response);
            echo "</pre>";
	}
	
	public function getCancel()
	{
	   // Do your thing
	}
```
