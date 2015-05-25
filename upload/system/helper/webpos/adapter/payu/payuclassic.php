<?php
class payuClassic {
	
	public function methodResponse($bank){
		$response=array();
		$response['message']='';
		$url = $bank['payu_alu_url'];
		//$url = "https://secure.payu.com.tr/order/alu/v2";
		$secretKey = $bank['payu_secret_key'];
		if(($bank['cc_type']==1) || ($bank['cc_type']==2)){
			$cardType="CCVISAMC";
		} else if($bank['cc_type']==3){
			$cardType="CCAMEX";
		} else if($bank['cc_type']==4){
			$cardType="CCDINERS";
		} else if($bank['cc_type']==5){
			$cardType="CCJB";
		}
		/*
		CCVISAMC – Visa/MasterCard Kartı (varsayılan)
		CCAMEX – AMEX Kartı
		CCDINERS - Diners Club Kartı 
		CCJB – JCB Kart
		*/
		$product_name="";
		$product_code="";
		$product_info="";
		foreach($bank['products']['products'] as $product) {
			$product_name.=$product['model'].'(x'.$product['quantity'].').';
			$product_code.=$product['model'].'x'.$product['quantity'];
			$product_info.=$product['model'].', '.$product['quantity'].'pcs. '.$product['name'];
			if(!empty($product['option'])) {
				$option_info="";
				foreach($product['option'] as $option){
					$option_info.=','.$option['name'].':'.$option['value'];	
				}
			$product_info.=	$option_info;
			}
			$product_info.=PHP_EOL;
		}
		$arParams = array(
		//The Merchant's ID
		"MERCHANT" => $bank['payu_merchant_id'],
		//order external reference number in Merchant's system
		"ORDER_REF" => $bank['order_id'],
		"ORDER_DATE" => gmdate('Y-m-d H:i:s'),
		
		//First product details begin
		"ORDER_PNAME[0]" => $product_name,
		"ORDER_PCODE[0]" => $product_code,
		"ORDER_PINFO[0]" => $product_info,
		"ORDER_PRICE[0]" => $bank['total'],
		"ORDER_QTY[0]" => "1",
		//First product details end

		"PRICES_CURRENCY" => $bank['order_info']['currency_code'],
		"PAY_METHOD" => $cardType,//to remove
		"CC_NUMBER" => $bank['cc_number'],
		"EXP_MONTH" => $bank['cc_expire_date_month'],
		"EXP_YEAR" => "20".$bank['cc_expire_date_year'],
		"CC_CVV" => $bank['cc_cvv2'],
		"CC_OWNER" => $bank['cc_owner'],
		
		//Return URL on the Merchandt webshop side that will be used in case of 3DS enrolled cards authorizations.
		"BACK_REF" => $bank['success_url'],
		"CLIENT_IP" => $bank['customer_ip'],
		"BILL_LNAME" => $bank['order_info']['firstname'],
		"BILL_FNAME" => $bank['order_info']['lastname'],
		"BILL_EMAIL" => $bank['order_info']['email'],
		"BILL_PHONE" => $bank['order_info']['telephone'],
		"BILL_COUNTRYCODE" => $bank['order_info']['payment_iso_code_2'], 
		
		//Delivery information
		"DELIVERY_FNAME" => $bank['order_info']['shipping_firstname'],
		"DELIVERY_LNAME" => $bank['order_info']['shipping_lastname'],
		"DELIVERY_PHONE" => $bank['order_info']['telephone'],
		"DELIVERY_ADDRESS" => $bank['order_info']['shipping_address_1'].$bank['order_info']['shipping_address_2'],
		"DELIVERY_ZIPCODE" => $bank['order_info']['shipping_postcode'],
		"DELIVERY_CITY" => $bank['order_info']['shipping_city'],
		"DELIVERY_STATE" => $bank['order_info']['shipping_zone'],
		"DELIVERY_COUNTRYCODE" => $bank['order_info']['shipping_iso_code_2']
		);
		if($bank['instalment']!=0){
			$arParams["SELECTED_INSTALLMENTS_NUMBER"]=$bank['instalment'];
		}
		if ($bank['mode']!='live') {
			$arParams["TESTORDER"]=1;
			$arParams["PRICES_CURRENCY"]="TRY";
		} 

		//begin HASH calculation
		ksort($arParams);

		$hashString = "";

		foreach ($arParams as $key=>$val) {
			$hashString .= strlen($val) . $val;
		}

		$arParams["ORDER_HASH"] = hash_hmac("md5", $hashString, $secretKey);
		//end HASH calculation

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);//prevent Poddle attack, have to set 0 to use TLS instead of SSL3 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
		$response_payu = curl_exec($ch);

		$curlerrcode = curl_errno($ch);
		$curlerr = curl_error($ch);

		if (empty($curlerr) && empty($curlerrcode)) {
			$parsedXML = @simplexml_load_string($response_payu);
			if ($parsedXML !== FALSE) {

				//Get PayU Transaction reference.
				//Can be stored in your system DB, linked with your current order, for match order in case of 3DSecure enrolled cards
				//Can be empty in case of invalid parameters errors
				$payuTranReference = $parsedXML->REFNO;

				if ($parsedXML->STATUS == "SUCCESS") {

					//In case of 3DS enrolled cards, PayU will return the extra XML tag URL_3DS that contains a unique url for each 
					//transaction. For example https://secure.payu.com.tr/order/alu_return_3ds.php?request_id=2Xrl85eakbSBr3WtcbixYQ%3D%3D.
					//The merchant must redirect the browser to this url to allow user to authenticate. 
					//After the authentification process ends the user will be redirected to BACK_REF url
					//with payment result in a HTTP POST request - see 3ds return sample. 
					if (($parsedXML->RETURN_CODE == "3DS_ENROLLED") && (!empty($parsedXML->URL_3DS))) {
						//header("Location:" . $parsedXML->URL_3DS);
						//die();
						$response['payu3d']=$parsedXML->URL_3DS;

					} else {
						
						$response['message'] = "SUCCESS [PayU reference number: " . $payuTranReference . "]";
						$response['redirect'] = 'success';
					}

					
				} else {
					$response['error'] = "FAILED: " . $parsedXML->RETURN_MESSAGE . " [" . $parsedXML->RETURN_CODE . "]";
					if (!empty($payuTranReference)) {
						//the transaction was register to PayU system, but some error occured during the bank authorization.
						//See $parsedXML->RETURN_MESSAGE and $parsedXML->RETURN_CODE for details               

						$response['error'].= " [PayU reference number: " . $payuTranReference . "]";
					}
				}
			}
		} else {
			//Was an error comunication between servers
			$response['error']= "cURL error: " . $curlerr;
		}
		return $response;
	}
	
	public function bankResponse($bank_response,$bank){
		$response=array();
		$response['message']='';
		if (!isset($bank_response['HASH']) || !empty($bank_response['HASH'])) {

			//begin HASH verification
			$arParams = $bank_response;
			unset($arParams['HASH']);

			$hashString = "";
			foreach ($arParams as $val) {
				$hashString .= strlen($val) . $val;
			}

			$secretKey = $bank['payu_secret_key'];
			$expectedHash = hash_hmac("md5", $hashString, $secretKey);
			if ($expectedHash != $bank_response["HASH"]) {
				/*echo "FAILED. Hash mismatch";
				die;*/
				$response['result']=0;
			$response['message']= "FAILED. Hash mismatch";
			} else {
				//end hash verification
				
				//Use the information below to match against your database record.
				$payuTranReference = $bank_response['REFNO'];
				$amount = $bank_response['AMOUNT'];
				$currency = $bank_response['CURRENCY'];
				$installments_no = $bank_response['INSTALLMENTS_NO'];

				if ($bank_response['STATUS'] == "SUCCESS") {
					//Update status of the transaction in your database.
				$response['result']=1;
				$response['message']= "SUCCESS [PayU reference number: " . $payuTranReference . "]";
				} else {
					$response['result']=0;
					$response['message']="FAILED ". $bank_response['RETURN_MESSAGE'] ."[". $bank_response['RETURN_CODE'] ."]";
					$response['message'].= " [PayU reference number: " . $payuTranReference . "]";
				}
			}
		} else {
			$response['result']=0;
			$response['message']="FAILED. Hash missing";
		}
		return $response;
	}
}