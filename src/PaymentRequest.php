<?php
namespace SahusoftCom\PayPal;

use SahusoftCom\PayPal\PayPalHttpPost;

class PaymentRequest
{
	public function __construct($apiContext)
	{
		$this->APIUSERNAME = $apiContext->APIUSERNAME;
		$this->APIPASSWORD = $apiContext->APIPASSWORD;
		$this->APISIGNATURE = $apiContext->APISIGNATURE;
		$this->ENDPOINT = $apiContext->ENDPOINT;
		$this->VERSION = $apiContext->VERSION;
		$this->REDIRECTURL = $apiContext->REDIRECTURL;

		$this->credStr = "USER=" . $this->APIUSERNAME . "&PWD=" . $this->APIPASSWORD . "&SIGNATURE=" . $this->APISIGNATURE . "&VERSION=" . $this->VERSION;
	}

	public function execute($data) 
	{
		$data['PAYMENT_ACTION'] = "sale";
		$data['METHOD'] = "SetExpressCheckout";

		if ( empty($data['METHOD']) )
			return ['status' => false, 'message' => "METHOD is required!"];

		if ( empty($data['RETURNURL']) )
			return ['status' => false, 'message' => 'RETURNURL is required!'];

		if ( empty($data['CANCELURL']) )
			return ['status' => false, 'message' => 'CANCELURL is required!'];

		if ( empty($data['CURRENCY']) )
			return ['status' => false, 'message' => 'CURRENCY is required!'];

		if ( empty($data['TOTAL_AMOUNT']) )
			return ['status' => false, 'message' => 'TOTAL_AMOUNT is required!'];
		
		if ( empty($data['AMOUNT']) )
			return ['status' => false, 'message' => 'AMOUNT is required!'];					

		if ( empty($data['PAYMENT_ACTION']) )
			return ['status' => false, 'message' => 'PAYMENT_ACTION is required!'];

		if ( empty($data['ITEM_LIST']) || count($data['ITEM_LIST']) <= 0 )
			return ['status' => false, 'message' => 'Atleast one item in ITEM_LIST is required!'];

		$nvpStr = "&METHOD=SetExpressCheckout"									// set SALE

		. "&RETURNURL=$data[RETURNURL]"
		. "&CANCELURL=$data[CANCELURL]"

		. "&PAYMENTREQUEST_0_CURRENCYCODE=$data[CURRENCY]"					// set USD
		. "&PAYMENTREQUEST_0_AMT=$data[TOTAL_AMOUNT]"
		. "&PAYMENTREQUEST_0_ITEMAMT=$data[AMOUNT]"
		. "&PAYMENTREQUEST_0_TAXAMT=$data[TAX_AMOUNT]"
		. "&PAYMENTREQUEST_0_PAYMENTACTION=$data[PAYMENT_ACTION]";

		if ( !empty($data['DESCRIPTION']) )
			$nvpStr .= "&PAYMENTREQUEST_0_DESC=$data[DESCRIPTION]";
			
		if ( !empty($data['NOSHIPPING']) )
			$nvpStr .= "&NOSHIPPING=$data[NOSHIPPING]";						// set 1
		else
			$nvpStr .= "&NOSHIPPING=1";

		$nvpStr .= "&ADDROVERRIDE=0";										// set 0

		if ( !empty($data['LOGOIMG']) )
			$nvpStr .= "&LOGOIMG=$data[LOGOIMG]";							// set Business Logo

		if ( !empty($data['BRANDNAME']) )
			$nvpStr .= "&BRANDNAME=$data[BRANDNAME]";						// set Business Name

		$i = 0;
		foreach ( $data['ITEM_LIST'] as $item ) {
			
			// $string = "&L_PAYMENTREQUEST_0_ITEMCATEGORY$i=Digital";
			$string = '';

			if ( !empty($item['NAME']) )
				$string .= "&L_PAYMENTREQUEST_0_NAME$i=$item[NAME]";

			if ( !empty($item['ITEMURL']) )
				$string .= "&L_PAYMENTREQUEST_0_ITEMURL$i=$item[ITEMURL]";	// Product URL

			if ( !empty($item['NUMBER']) )
				$string .= "&L_PAYMENTREQUEST_0_NUMBER$i=$item[NUMBER]";

			if ( !empty($item['QUANTITY']) )
				$string .= "&L_PAYMENTREQUEST_0_QTY$i=$item[QUANTITY]";

			if ( !empty($item['TAX_AMOUNT']) )
				$string .= "&L_PAYMENTREQUEST_0_TAXAMT$i=$item[TAX_AMOUNT]";

			if ( !empty($item['AMOUNT']) )
				$string .= "&L_PAYMENTREQUEST_0_AMT$i=$item[AMOUNT]";

			if ( !empty($item['DESCRIPTION']) )
				$string .= "&L_PAYMENTREQUEST_0_DESC$i=$item[DESCRIPTION]";

			$nvpStr .= $string;
			$i++;
		}		
		
		// Combine the two strings and make the API Call
		$reqStr = $this->credStr . $nvpStr;
		$response = PayPalHttpPost::handle($this->ENDPOINT, $reqStr);		

		// Check Response
		if ( $response['ACK'] == "Success" || $response['ACK'] == "SuccessWithWarning" ) {
			
			// Setup Redirect URL
			$redirectURL = $this->REDIRECTURL . urldecode($response['TOKEN']);			
			header('Location: ' . $redirectURL, true, 302);
			exit();

		} else if($response['ACK'] == "Failure" || $response['ACK'] == "FailureWithWarning") {
			
			return ['response' => $response, 'message' => "The API Call Failed"];
		}
	}
}