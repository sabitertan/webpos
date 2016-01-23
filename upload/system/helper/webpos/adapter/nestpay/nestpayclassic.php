<?php
class nestpayClassic {
	
	public function methodResponse($bank){
		$response=array();
		$action='';
		if ($bank['mode']=='live') {
			$action=$bank['nestpay_classic_url'];
		} else if ($bank['mode']=='test') {
			$action=$bank['nestpay_test_url'];
		}
		$xml_fields=array('name' => $bank['nestpay_classic_name'],
		'password' => $bank['nestpay_classic_password'],
		'clientid' => $bank['nestpay_client_id'],
		'url' => $action,
		'mode' => 'P',
		'type' => 'Auth',
		'pan'=>$bank['cc_number'],
		'expires' => $bank['cc_expire_date_month'].'/'.$bank['cc_expire_date_year'],
		'cv2' => $bank['cc_cvv2'],
		'cardType'=>$bank['cc_type'],
		'ip' => $bank['customer_ip'],
		'tutar' => $bank['total'],
		'taksit' => $bank['instalment'],	
		'oid' => $bank['order_id'],
		'email' => ''
		);
		//field
		$xml_response=$this->xmlSend($xml_fields);
		$xml = simplexml_load_string($xml_response);
		
		$Response=isset($xml->Response)?(string)$xml->Response:'';
		$OrderId=isset($xml->OrderId)?(string)$xml->OrderId:'';
		$AuthCode=isset($xml->AuthCode)?(string)$xml->AuthCode:'';
		$ProcReturnCode=isset($xml->ProcReturnCode)?(string)$xml->ProcReturnCode:'';
		$ErrMsg=isset($xml->ErrMsg)?(string)$xml->ErrMsg:'';
		$HostRefNum=isset($xml->HostRefNum)?(string)$xml->HostRefNum:'';
		$TransId=isset($xml->TransId)?(string)$xml->TransId:'';
		$response['message'] = '';
		if($ProcReturnCode =="00" || $Response === "Approved") {
			$response['result']=1;
			$response['message'].='Ödeme Başarılı<br/>';
			$response['message'].='AuthCode : '.$AuthCode.'<br/>';
			$response['message'].='Response : '.$Response.'<br/>';
			$response['message'].='HostRefNum : '.$HostRefNum.'<br/>';
			$response['message'].='ProcReturnCode : '.$ProcReturnCode.'<br/>';
			$response['message'].='TransId : '.$TransId.'<br/>';
			$response['message'].='ErrMsg : '.$ErrMsg.'<br/>';
			$response['redirect']='success';
		} else {
			$response['result']=0;
			$response['message'].='Ödeme Başarısız.<br/>';
			$response['message'].='ErrMsg : '.$ErrMsg.'<br/>';
			$response['error']='error';
		}
		//$response['form']=;
		
		return $response;
		
	}

	private function xmlSend($fields){

		$request= "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>".
		"<CC5Request>".
		"<Name>".$fields['name']."</Name>".
		"<Password>".$fields['password']."</Password>".
		"<ClientId>".$fields['clientid']."</ClientId>".
		"<IPAddress>".$fields['ip']."</IPAddress>".
		"<Email>".$fields['email']."</Email>".
		"<Mode>".$fields['mode']."</Mode>".
		"<OrderId>".$fields['oid']."</OrderId>".
		"<GroupId></GroupId>".
		"<TransId></TransId>".
		"<UserId></UserId>".
		"<Type>".$fields['type']."</Type>".
		"<Number>".$fields['pan']."</Number>".
		"<Expires>".$fields['expires']."</Expires>".
		"<Cvv2Val>".$fields['cv2']."</Cvv2Val>".
		"<Total>".$fields['tutar']."</Total>".
		"<Currency>949</Currency>".
		"<Taksit>".$fields['taksit']."</Taksit>".
		"<BillTo>".
		"<Name></Name>".
		"<Street1></Street1>".
		"<Street2></Street2>".
		"<Street3></Street3>".
		"<City></City>".
		"<StateProv></StateProv>".
		"<PostalCode></PostalCode>".
		"<Country></Country>".
		"<Company></Company>".
		"<TelVoice></TelVoice>".
		"</BillTo>".
		"<ShipTo>".
		"<Name></Name>".
		"<Street1></Street1>".
		"<Street2></Street2>".
		"<Street3></Street3>".
		"<City></City>".
		"<StateProv></StateProv>".
		"<PostalCode></PostalCode>".
		"<Country></Country>".
		"</ShipTo>".
		"<Extra></Extra>".
		"</CC5Request>";
		// URL below is payment gateway's adress ( API Server), it is NOT 3D Gateway.		
		$url = $fields['url'];
		
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL,$url); 		// set url to post to
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);//prevent Poddle attack, have to set 0 to use TLS instead of SSL3 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); 		// times out after 90s
		curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($request)); // add POST fields
		
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$result='<CC5Response><ErrMsg>cUrl Error: '.curl_error($ch).'</ErrMsg></CC5Response>';
		}
		
		curl_close($ch);
		return $result;
	}
}
