<?php
namespace SahusoftCom\PayPal;

use SahusoftCom\PayPal\PayPalHttpPost;

class PaymentResponse  {
	
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

	public function handle($data)
	{
		$token = $_GET['token'];
		$payerid = $_GET['PayerID'];
		
		$nvpStr = "&METHOD=GetExpressCheckoutDetails&TOKEN=" . urldecode($token);
		
		$reqStr = $this->credStr . $nvpStr;
		$response = PayPalHttpPost::handle($this->ENDPOINT, $reqStr);
		
		$total = urldecode($response['PAYMENTREQUEST_0_AMT']);
		
		$nvpStr = "&METHOD=DoExpressCheckoutPayment"						// set SALE
			. "&TOKEN=$token"
			. "&PAYERID=$payerid"
			. "&RETURNURL=$data[RETURNURL]"
			. "&CANCELURL=$data[CANCELURL]"

			. "&PAYMENTREQUEST_0_CURRENCYCODE=$data[CURRENCY]"					// set USD
			. "&PAYMENTREQUEST_0_AMT=$data[TOTAL_AMOUNT]"
			. "&PAYMENTREQUEST_0_ITEMAMT=$data[AMOUNT]"
			. "&PAYMENTREQUEST_0_TAXAMT=$data[TAX_AMOUNT]"
			. "&PAYMENTREQUEST_0_PAYMENTACTION=$data[PAYMENT_ACTION]";

			if ( !empty($data['NOTIFYURL']) )
				$nvpStr .= "&PAYMENTREQUEST_0_NOTIFYURL=$data[NOTIFYURL]";

			if ( !empty($data['DESCRIPTION']) )
				$nvpStr .= "&PAYMENTREQUEST_0_DESC=$data[DESCRIPTION]";

			if ( !empty($data['NOTIFYURL']) )
				$nvpStr .= "&PAYMENTREQUEST_0_NOTIFYURL=$data[NOTIFYURL]";
				
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

		$reqStr = $this->credStr . $nvpStr;
		$doresponse = PayPalHttpPost::handle($this->ENDPOINT, $reqStr);

		if ( !empty($doresponse) && ($doresponse['ACK'] == "Success" || $doresponse['ACK'] == "SuccessWithWarning") ) {

			$transactionId = urldecode($doresponse['PAYMENTINFO_0_TRANSACTIONID']);
			$nvpStr = "&METHOD=GetTransactionDetails&TRANSACTIONID=" . $transactionId;			    
		    
		    $reqStr = $this->credStr . $nvpStr;
		    
		    $response = [];
			$response = PayPalHttpPost::handle($this->ENDPOINT, $reqStr);
			if ( !empty($response) && ($response['ACK'] == "Success" || $response['ACK'] == "SuccessWithWarning") ) {

				$doResponse['TRANSACTIONID'] = $transactionId;
				$doResponse['FIRSTNAME'] = !empty($response['FIRSTNAME']) ? urldecode($response['FIRSTNAME']) : '';
				$doResponse['LASTNAME'] = !empty($response['LASTNAME']) ? urldecode($response['LASTNAME']) : '';
				$doResponse['EMAIL'] = !empty($response['EMAIL']) ? urldecode($response['EMAIL']) : '';
				$doResponse['ACK'] = !empty($response['ACK']) ? urldecode($response['ACK']) : '';
				$doResponse['PAYMENTREQUEST_0_AMT'] = !empty($response['AMT']) ? urldecode($response['AMT']) : '';
				$doResponse['PAYMENTREQUEST_0_CURRENCYCODE'] = !empty($response['CURRENCYCODE']) ? urldecode($response['CURRENCYCODE']) : '';
				return $doResponse;
			}
		}

		header('Location: ' . $data['CANCELURL'].'?'.(!empty(http_build_query($doresponse)) ? http_build_query($doresponse) : ''), true, 302);
		exit();
	}
}