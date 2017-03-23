<?php
namespace SahusoftCom\PayPal;

use SahusoftCom\PayPal\PayPalHttpPost;

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

class Return  {

	//set GET var's to local vars:
	$token = $_GET['token'];
	$payerid = $_GET['PayerID'];
	
	//Build NVP String for GetExpressCheckoutDetails
	$nvp_str = "&METHOD=GetExpressCheckoutDetails&TOKEN=" . urldecode($token);
	//combine the two strings and make the API Call
	$req_str = $this->cred_str . $nvp_str;
	$response = PayPalHttpPost::handle($this->ENDPOINT, $req_str);
	//get total
	$total = urldecode($response['PAYMENTREQUEST_0_AMT']);
	//print_r($response);
	//Display totals to user and request a "confirm"
	echo "<html><head><title>Confirm your payment</title></head><body><table border='1'>";
	echo "<tr><td colspan='5'>Confirm Your Purchase</td></tr>";
	echo "<tr><td colspan='3'>Total:</td><td colspan='2'>" . $total . "</td></tr>";
	echo "</table>";
	echo "<form action='' method='post'><input type='submit' name='confirm' value='Confirm' /></form>";
	echo "</body></html>";
	if(isset($_POST['confirm']) && $_POST['confirm'] == "Confirm") {
	//set NVP Str PLEASE NOTE, This is HARD CODED for this Example, You would want to Dynamically Build the request string
	//based on the API Response from GetExpressCheckoutDetails
	$doec_str = $cred_str
	. "&METHOD=DoExpressCheckoutPayment"
	. "&TOKEN=" . $token
	. "&PAYERID=" . $payerid
	. "&PAYMENTREQUEST_0_CURRENCYCODE=USD"
	. "&PAYMENTREQUEST_0_AMT=300"
	. "&PAYMENTREQUEST_0_ITEMAMT=200"
	. "&PAYMENTREQUEST_0_TAXAMT=100"
	. "&PAYMENTREQUEST_0_DESC=Movies"
	. "&PAYMENTREQUEST_0_PAYMENTACTION=Sale"
	. "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital"
	. "&L_PAYMENTREQUEST_0_ITEMCATEGORY1=Digital"
	. "&L_PAYMENTREQUEST_0_NAME0=Kitty Antics"
	. "&L_PAYMENTREQUEST_0_NAME1=All About Cats"
	. "&L_PAYMENTREQUEST_0_NUMBER0=101"
	. "&L_PAYMENTREQUEST_0_NUMBER1=102"
	. "&L_PAYMENTREQUEST_0_QTY0=1"
	. "&L_PAYMENTREQUEST_0_QTY1=1"
	. "&L_PAYMENTREQUEST_0_TAXAMT0=50"
	. "&L_PAYMENTREQUEST_0_TAXAMT1=50"
	. "&L_PAYMENTREQUEST_0_AMT0=100"
	. "&L_PAYMENTREQUEST_0_AMT1=100"
	. "&L_PAYMENTREQUEST_0_DESC0=Download"
	. "&L_PAYMENTREQUEST_0_DESC1=Download";
	//make the DoEC Call:
	$doresponse = PayPalHttpPost::handle($this->ENDPOINT, $doec_str);
	//check Response
	if($doresponse['ACK'] == "Success" || $doresponse['ACK'] == "SuccessWithWarning") {
	echo "Your Payment Has Completed! click <a href='#'>HERE</a> to download your goods";
	//place in logic to make digital goods available
	} else if($doresponse['ACK'] == "Failure" || $doresponse['ACK'] == "FailureWithWarning") {
	echo "The API Call Failed";
	print_r($doresponse);
	} else {
	echo "";
	}
}
