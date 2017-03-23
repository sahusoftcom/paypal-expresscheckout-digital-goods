<?php
namespace SahusoftCom\PayPal;

class PayPalHttpPost
{
	public static function handle($myEndpoint, $myApiStr)
	{	
		\Log::info('message');
		// setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $myEndpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		
		// turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		// setting the NVP $myApiStr as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $myApiStr);
		
		// getting response from server
		$httpResponse = curl_exec($ch);
		if ( !$httpResponse ) {

			$response = "$API_method failed: ".curl_error($ch).'('.curl_errno($ch).')';
			return $response;
		}

		\Log::info('httpResponse');
		\Log::info($httpResponse);

		$httpResponseAr = explode("&", $httpResponse);
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {

			$tmpAr = explode("=", $value);
			if ( sizeof($tmpAr) > 1 ) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if ( (0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr) ) {

			$response = "Invalid HTTP Response for POST request($myApiStr) to $API_Endpoint.";
			return $response;
		}

		\Log::info('httpParsedResponseAr');
		\Log::info($httpParsedResponseAr);

		return $httpParsedResponseAr;
	}
}