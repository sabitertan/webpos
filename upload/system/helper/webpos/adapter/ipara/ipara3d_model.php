<?php
class ipara3DModel {
	private $card;
	private $customer_ip;
	private $amount;
	private function createForm($bank) {
		$public_key=$bank['ipara_public_key'];
		$private_key=$bank['ipara_private_key'];
		$success_url=$bank['success_url'];
		$fail_url=$bank['fail_url'];
		$order_id=$bank['order_id'];
		$amount=$bank['total'];
		//card
		$this->card = array();
        $this->card['owner_name'] = $bank['cc_owner'];
        $this->card['number'] = $bank['cc_number'];
        $this->card['expire_month'] = $bank['cc_expire_date_month'];
        $this->card['expire_year'] = $bank['cc_expire_date_year'];
        $this->card['cvc'] = $bank['cc_cvv2'];
		$this->amount=$amount;
		$this->customer_ip=$bank['customer_ip'];
		//
		if($bank['instalment']!=0){
			$taksit=$bank['instalment'];
		} else {
			$taksit=1;
		}
		$action="https://www.ipara.com/3dgate";
		$mode="P";
		if ($bank['mode']=='test') {
			$mode="T";
		}
		$timestamp = date("Y-m-d H:i:s");
		$hash_text = $private_key . $order_id . number_format((float)$amount, 2, '', '') . $mode . $bank['cc_owner'] .
		$bank['cc_number'] . $bank['cc_expire_date_month'] . $bank['cc_expire_date_year'] . $bank['cc_cvv2'] .
		$bank['order_info']['firstname'] . $bank['order_info']['lastname'] . $bank['order_info']['email'] . $timestamp;
		$token = $public_key . ":" . base64_encode(sha1($hash_text, true));
		
		$inputs=array();
		//test info pan: 4508034508034509 expire: 12/16 cv2:000 3dpass:a
		$inputs=array('orderId'=>$order_id,
		'amount'=>number_format((float)$amount, 2, '', ''),
		'cardOwnerName'=>urlencode($bank['cc_owner']),
		'cardNumber'=>$bank['cc_number'],
		'cardExpireMonth'=>$bank['cc_expire_date_month'],
		'cardExpireYear'=>$bank['cc_expire_date_year'],
		'installment'=>$taksit,
		'cardCvc'=>$bank['cc_cvv2'],
		'mode'=>$mode,
		'purchaserName'=>$bank['order_info']['firstname'],
		'purchaserSurname'=>$bank['order_info']['lastname'],
		'purchaserEmail'=>$bank['order_info']['email'],
		'successUrl'=>$success_url,
		'failureUrl'=>$fail_url,
		'version'=>"1.0",
		'transactionDate'=>$timestamp,
		'token'=>$token
		);

		
		$form='<form id="webpos_form" name="webpos_form" method="post" action="'.$action.'">';
		foreach($inputs as $key=>$value){ 

			$form.='<input type="hidden" name="'.$key.'" value="'.$value.'" />';

		} 
		$form.='</form>';
		return $form;
		
	}
	public function methodResponse($bank){
		date_default_timezone_set('Europe/Istanbul');
		$response=array();
		$response['form']=$this->createForm($bank);
		//$response['redirect']=;
		//$response['error']=;
		return $response;
		
	}
	public function bankResponse($bank_response, $bank){
		
		date_default_timezone_set('Europe/Istanbul');
		$response=array();
		$response['message']='';
		// hash control
		//TODO remove -> 
		$bank_response['result'] = $bank_response['result'];
		$bank_response['order_id'] = $bank_response['orderId'];
		$bank_response['amount'] = $bank_response['amount'];
		$bank_response['mode'] = $bank_response['mode'];
		// <- remove
		$bank_response['public_key'] = isset($bank_response['publicKey'])?$bank_response['publicKey']:"";
		$bank_response['echo'] = isset($bank_response['echo'])?$bank_response['echo']:"";
		$bank_response['error_code'] = isset($bank_response['errorCode'])?$bank_response['errorCode']:"";
		$bank_response['error_message'] = isset($bank_response['errorMessage'])?$bank_response['errorMessage']:"";
		$bank_response['transaction_date'] = isset($bank_response['transactionDate'])?$bank_response['transactionDate']:"";
		$bank_response['hash'] = isset($bank_response['hash'])?$bank_response['hash']:"";
		$bank_response['three_d_secure_code'] = isset($bank_response['threeDSecureCode'])?$bank_response['threeDSecureCode']:"";
		//$bank_response['amount'] = number_format((float)($bank_response['amount'] / 100), 2, '.', '');
		
		
		if ($bank_response['hash'] != NULL) {
			$hash_text = $bank_response['order_id'] . $bank_response['result'] . $bank_response['amount'] . $bank_response['mode'] . $bank_response['error_code'] . $bank_response['error_message'] . $bank_response['transaction_date'] . $bank_response['public_key'] . $bank['ipara_private_key'];
			$hash = base64_encode(sha1($hash_text, true));
		} else {
			$response['message'].='Sayısal İmza tanımlı değil.<br/>';
			$hash="nohash_error";
		}
		
		if ($hash != $bank_response['hash']) {
			$response['message']="Ödeme cevabı hash doğrulaması hatalı. [result : " . $bank_response['result'] . ",error_code : " . $bank_response['error_code'] . ",error_message : " . $bank_response['error_message'] . "]";
			$response['message'].=$hash.'<br/>';
			$response['message'].=$bank_response['hash'].'<br/>';
			$response['result']=0;
		} else {

			if ($bank_response['result'] == 1){
				$response['message'].='3D Onayı Başarılı.<br/>';
				//field
				if($bank['instalment']!=0){
					$taksit=$bank['instalment'];
				} else {
					$taksit=1;
				}
				$xml_fields=array('mode' => $bank_response['mode'],
				'three_d_secure_code' => $bank_response['three_d_secure_code'],
				'order_id' => $bank['order_info']['order_id'],
				'amount' => $bank_response['amount'],
				'three_d' => "true",
				'installment'=>$taksit,
				'client_ip'=>$this->customer_ip,
				'public_key'=>$bank['ipara_public_key'],
				'private_key'=>$bank['ipara_private_key']
				);
				$xml_fields['products']=$bank['products'];
				$xml_fields['card']=array( 'owner_name'=> $this->card['owner_name'],
				'number'=>$this->card['number'],
				'expire_month'=>$this->card['expire_month'],
				'expire_year'=>$this->card['expire_year'],
				'cvc'=>$this->card['cvc']
				);
				$xml_fields['shipping_address']=array('name' => $bank['order_info']['shipping_firstname'],
				'surname' => $bank['order_info']['shipping_lastname'],
				'address' => $bank['order_info']['shipping_address_1'].$bank['order_info']['shipping_address_2'],
				'zipcode' => $bank['order_info']['shipping_postcode'],
				'city_text' => $bank['order_info']['shipping_city'].' '.$bank['order_info']['shipping_zone'],
				'country_code' => $bank['order_info']['shipping_iso_code_2'],
				'country_text' => $bank['order_info']['shipping_country'],
				'phone_number' => $bank['order_info']['telephone']
				);
				$xml_fields['invoice_address']=array('name' => $bank['order_info']['payment_firstname'],
				'surname' => $bank['order_info']['payment_lastname'],
				'address' => $bank['order_info']['payment_address_1'].$bank['order_info']['payment_address_2'],
				'zipcode' => $bank['order_info']['payment_postcode'],
				'city_text' => $bank['order_info']['payment_city'].' '.$bank['order_info']['payment_zone'],
				'country_code' => $bank['order_info']['payment_iso_code_2'],
				'company_name' => $bank['order_info']['payment_company'],
				'country_text' => $bank['order_info']['payment_country'],
				'phone_number' => $bank['order_info']['telephone']
				
				);
				$xml_fields['purchaser']=array('name' => $bank['order_info']['firstname'],
				'surname' => $bank['order_info']['lastname'],
				'email' => $bank['order_info']['email'],
				'gsm_number' => $bank['order_info']['telephone']
				
				);
				//field
				$xml=$this->xmlSend($xml_fields);
				
				$xml_response = new SimpleXMLElement($xml);
				if ($xml_response != NULL) {
					$iResult = $xml_response->result;
					$iOrderid = $xml_response->orderId;
					$iAmount = $xml_response->amount;
					$iMode = $xml_response->mode;
					$iPublicKey = $xml_response->publicKey;
					$iErrorCode = $xml_response->errorCode;
					$iErrorMessage = $xml_response->errorMessage;
					$iTransactionDate = $xml_response->transactionDate;
					$iHash = $xml_response->hash;
					if ($iHash != NULL) {
						$hash_text = $iOrderid . $iResult . $iAmount . $iMode . $iErrorCode . $iErrorMessage . $iTransactionDate . $iPublicKey . $bank['ipara_private_key'];
						$hash = base64_encode(sha1($hash_text, true));
					} else {
						$response['message'].='Sayısal İmza tanımlı değil(API).<br/>';
						$hash="nohash_error";
					}
					
					if ($hash != $iHash) {
						$response['message']="Ödeme cevabı hash doğrulaması hatalı(API). [result : " . $bank_response['result'] . ",error_code : " . $bank_response['error_code'] . ",error_message : " . $bank_response['error_message'] . "]";
						$response['result']=0;
					} else {
						if($iResult==1) {
							$response['result']=1;
							$response['message'].='Ödeme Başarılı<br/>';
							$response['message'].='Result : '.$iResult.'<br/>';
							$response['message'].='Amount : '.(number_format((float)($iAmount / 100), 2, '.', '')).'<br/>';
							$response['message'].='Mode : '.$iMode.'<br/>';
							$response['message'].='ErrorCode : '.$iErrorCode.'<br/>';
							$response['message'].='ErrMsg : '.$iErrorMessage.'<br/>';
							$response['message'].='TransDate : '.$iTransactionDate.'<br/>';
							
						} else {
							$response['result']=0;
							$response['message'].='Ödeme Başarısız.<br/>';
							$response['message'].='ErrorCode : '.$iErrorCode.'<br/>';
							$response['message'].='ErrMsg : '.$iErrorMessage.'<br/>';
						}
					}
				} else {
					$response['result']=0;
					$response['message'].='Ödeme cevabı xml formatında değil.<br/>';
				}
			} else {
				$response['result']=0;
				$response['message'].='3D doğrulama başarısız<br/>';
				$response['message'].=$bank_response['error_message'];
				
			}
		}
		//print_r($response);
		return $response;
	}
	private function xmlSend($field){
		//products
		 $xml_data_product_part="";
		foreach($field['products']['products'] as $product) {
			    $xml_data_product_part .= "<product>\n" .
                "	<productCode>" . urlencode($product['model']) . "</productCode>\n" .
                "	<productName>" . urlencode($product['name']) . "</productName>\n" .
                "	<quantity>" . $product['quantity'] . "</quantity>\n" .
                "	<price>" . number_format((float)$product['price'], 2, '', '') . "</price>\n" .
                "</product>\n";
		}
		//
date_default_timezone_set('Europe/Istanbul');
		$request= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
		"<auth>\n" .
		"    <threeD>" . $field['three_d'] . "</threeD>\n" .
		"    <orderId>" . $field['order_id'] . "</orderId>\n" .
		"    <amount>" . $field['amount'] . "</amount>\n" .
		"    <cardOwnerName>" . urlencode($field['card']['owner_name']) . "</cardOwnerName>\n" .
		"    <cardNumber>" . $field['card']['number'] . "</cardNumber>\n" .
		"    <cardExpireMonth>" . $field['card']['expire_month'] . "</cardExpireMonth>\n" .
		"    <cardExpireYear>" . $field['card']['expire_year'] . "</cardExpireYear>\n" .
		"    <installment>" . $field['installment'] . "</installment>\n" .
		"    <cardCvc>" . $field['card']['cvc'] . "</cardCvc>\n" .
		"    <mode>" . $field['mode'] . "</mode>\n" .
		"    <threeDSecureCode>" . $field['three_d_secure_code'] . "</threeDSecureCode>\n".
		"    <products>\n" .
            $xml_data_product_part .
        "    </products>\n" .
		"    <purchaser>\n" .
		"        <name>" . urlencode($field['purchaser']['name']) . "</name>\n" .
		"        <surname>" . urlencode($field['purchaser']['surname']) . "</surname>\n" .
		"        <email>" . $field['purchaser']['email'] . "</email>\n" .
		"        <gsmNumber>" . urlencode($field['purchaser']['gsm_number']) . "</gsmNumber>\n" .
		"        <clientIp>" . $field['client_ip'] . "</clientIp>\n" .
		"        <invoiceAddress>\n" .
		"            <name>" . urlencode($field['invoice_address']['name']) . "</name>\n" .
		"            <surname>" . urlencode($field['invoice_address']['surname']) . "</surname>\n" .
		"            <address>" . urlencode($field['invoice_address']['address']) . "</address>\n" .
		"            <zipcode>" . urlencode($field['invoice_address']['zipcode']) . "</zipcode>\n" .
		"            <city>" . urlencode($field['invoice_address']['city_text']) . "</city>\n" .
		"            <country>" . urlencode($field['invoice_address']['country_code']) . "</country>\n" .
		"            <companyName>" . urlencode($field['invoice_address']['company_name']) . "</companyName>\n" .
		"            <phoneNumber>" . urlencode($field['invoice_address']['phone_number']) . "</phoneNumber>\n" .
		"        </invoiceAddress>\n" .
		"        <shippingAddress>\n" .
		"            <name>" . urlencode($field['shipping_address']['name']) . "</name>\n" .
		"            <surname>" . urlencode($field['shipping_address']['surname']) . "</surname>\n" .
		"            <address>" . urlencode($field['shipping_address']['address']) . "</address>\n" .
		"            <zipcode>" . urlencode($field['shipping_address']['zipcode']) . "</zipcode>\n" .
		"            <city>" . urlencode($field['shipping_address']['city_text']) . "</city>\n" .
		"            <country>" . urlencode($field['shipping_address']['country_code']) . "</country>\n" .
		"            <phoneNumber>" . urlencode($field['shipping_address']['phone_number']) . "</phoneNumber>\n" .
		"        </shippingAddress>\n" .
		"    </purchaser>\n" .
		"</auth>";
		// URL below is payment gateway's adress ( API Server), it is NOT 3D Gateway.		
		$url = "https://api.ipara.com/rest/payment/auth";
		$timestamp = date("Y-m-d H:i:s");
		$token = "";
		$hash_text = $field['private_key'] . $field['order_id'] . $field['amount'] . $field['mode'] . $field['three_d_secure_code'] . $timestamp;
		$token = $field['public_key'] . ":" . base64_encode(sha1($hash_text, true));		
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL,$url); 		// set url to post to
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);//prevent Poddle attack, have to set 0 to use TLS instead of SSL3 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml", "transactionDate: " . $timestamp, "version: 1.0", "token: " . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); 		// times out after 90s
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // add POST fields
		
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$result='<Response><errorMessage>cUrl Error: '.curl_error($ch).'</errorMessage></Response>';
		}
		
		curl_close($ch);
		return $result;
	}
}