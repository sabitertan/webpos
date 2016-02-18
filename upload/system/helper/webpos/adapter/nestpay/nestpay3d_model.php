<?php
class nestpay3DModel {
	
	private function createHash($clientId,$storekey,$okUrl,$failUrl,$oid,$amount,$rnd) {
		$hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $rnd . $storekey; 
		$hash = base64_encode(pack('H*',sha1($hashstr)));
		return $hash;
	}
	
	private function createForm($bank) {
		$rnd = microtime();
		$clientId=$bank['nestpay_client_id'];
		$storekey=$bank['nestpay_3D_storekey'];
		$okUrl=$bank['success_url'];
		$failUrl=$bank['fail_url'];
		$oid=$bank['order_id'];
		$amount=$bank['total'];
		if($bank['instalment']!=0){
			$taksit=$bank['instalment'];
		} else {
			$taksit="";
		}
		$hash=$this->createHash($clientId,$storekey,$okUrl,$failUrl,$oid,$amount,$rnd);
		
		$inputs=array();
		//test info pan: 4508034508034509 expire: 12/16 cv2:000 3dpass:a
		$inputs=array('pan'=>$bank['cc_number'],
		'cv2'=>$bank['cc_cvv2'],
		'Ecom_Payment_Card_ExpDate_Year'=>$bank['cc_expire_date_year'],
		'Ecom_Payment_Card_ExpDate_Month'=>$bank['cc_expire_date_month'],
		'cardType'=>$bank['cc_type'],
		'clientid'=>$bank['nestpay_client_id'],
		'amount'=>$amount,
		'oid'=>$oid,
		'okUrl'=>$okUrl,
		'failUrl'=>$failUrl,
		'rnd'=>$rnd,
		'hash'=>$hash,
		'storetype'=>"3d",
		'lang'=>"tr",
		'currency'=>"949",
		'bank_id'=>$bank['bank_id']
		);
		$action='';
		if ($bank['mode']=='live') {
			$action=$bank['nestpay_3D_url'];
		} else if ($bank['mode']=='test') {
			$action=$bank['nestpay_test_url'];
		}
		
		$form='<form id="webpos_form" name="webpos_form" method="post" action="'.$action.'">';
		foreach($inputs as $key=>$value){ 

			$form.='<input type="hidden" name="'.$key.'" value="'.$value.'" />';

		} 
		$form.='</form>';
		return $form;
		
	}
	public function methodResponse($bank){
		$response=array();
		$response['form']=$this->createForm($bank);
		//$response['redirect']=;
		//$response['error']=;
		return $response;
		
	}
	public function bankResponse($bank_response,$bank){
		$response=array();
		$response['message']='';
		// hash control
		//$hashparams = $bank_response['HASHPARAMS'];
		$hashparamsval = $bank_response['HASHPARAMSVAL'];
		$hashparam = $bank_response['HASH'];
		$storekey=$bank['nestpay_3D_storekey'];
		$hashval = $hashparamsval.$storekey;
		
		$hash = base64_encode(pack('H*',sha1($hashval))); // Hash value
		
		if($hashparam != $hash) {
			$response['message']=$bank_response['mdErrorMsg'] .'<h4>Güvenlik Uyarısı. Sayısal İmza Geçersiz !</h4>' ;
			$response['result']=0;
		} else {
			$mdStatus=$bank_response['mdStatus'];// if mdStatus 1,2,3,4 then 3D authentication is successful, if mdStatus 5,6,7,8,9,0 then 3D authentication is FAILED
			$mdArray=array('1','2','3','4');
			if (in_array($mdStatus,$mdArray)){
				$response['message'].='3D Onayı Başarılı.<br/>';
				//field
				$xml_fields=array('name' => $bank['nestpay_classic_name'],
				'password' => $bank['nestpay_classic_password'],
				'clientid' => $bank['nestpay_client_id'],
				'url' => $bank['nestpay_classic_url'],
				'mode' => 'P',
				'type' => 'Auth',
				'expires' => $bank_response['Ecom_Payment_Card_ExpDate_Month'].'/'.$bank_response['Ecom_Payment_Card_ExpDate_Year'],
				'cv2' => $bank_response['cv2'],
				'ip' => $bank_response['clientIp'],
				'tutar' => $bank_response['amount'],
				'taksit' => $bank_response['taksit'],	
				'oid' => $bank_response['oid'],
				'email' => '',
				'xid' => $bank_response['xid'],
				'eci' => $bank_response['eci'],
				'cavv' =>$bank_response['cavv'],
				'md' => $bank_response['md']
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
				
				if($ProcReturnCode =="00" || $Response === "Approved") {
					$response['result']=1;
					$response['message'].='Ödeme Başarılı<br/>';
					$response['message'].='AuthCode : '.$AuthCode.'<br/>';
					$response['message'].='Response : '.$Response.'<br/>';
					$response['message'].='HostRefNum : '.$HostRefNum.'<br/>';
					$response['message'].='ProcReturnCode : '.$ProcReturnCode.'<br/>';
					$response['message'].='TransId : '.$TransId.'<br/>';
					$response['message'].='ErrMsg : '.$ErrMsg.'<br/>';
				} else {
					$response['result']=0;
					$response['message'].='Ödeme Başarısız.<br/>';
					$response['message'].='ErrMsg : '.$ErrMsg.'<br/>';
				}
				
				
				
			} else {
				$response['result']=0;
				$response['message'].='3D doğrulama başarısız<br/>';
				$response['message'].=$bank_response['mdErrorMsg'];
				
			}
		}
		//print_r($response);
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
		"<Number>".$fields['md']."</Number>".
		"<Expires></Expires>".
		"<Cvv2Val></Cvv2Val>".
		"<Total>".$fields['tutar']."</Total>".
		"<Currency>949</Currency>".
		"<Taksit>".$fields['taksit']."</Taksit>".
		"<PayerTxnId>".$fields['xid']."</PayerTxnId>".
		"<PayerSecurityLevel>".$fields['eci']."</PayerSecurityLevel>".
		"<PayerAuthenticationCode>".$fields['cavv']."</PayerAuthenticationCode>".
		"<CardholderPresentCode>13</CardholderPresentCode>".
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
