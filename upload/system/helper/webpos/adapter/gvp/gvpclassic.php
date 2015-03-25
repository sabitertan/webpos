<?php
class gvpClassic {
	private function createHash($oid, $terminal_id,$cardnumber,$amount,$provaut_password) {
		$secData=strtoupper(sha1($provaut_password."0".$terminal_id));
		$hashstr = $oid . $terminal_id. $cardnumber. $amount . $secData;
		$hash = strtoupper(sha1($hashstr));
		return $hash;
	}

	public function methodResponse($bank){
		$response=array();
		$response['message']='';
				if($bank['instalment']!=0){
			$instalment=$bank['instalment'];
		} else {
			$instalment="";
		}
		$amount=(int)($bank['total']*100);
		$action='';
		if ($bank['mode']=='live') {
			$action=$bank['gvp_classic_url'];
		} else if ($bank['mode']=='test') {
			$action=$bank['gvp_test_url'];
		}
		$hash=$this->createHash($bank['order_id'],$bank['gvp_terminal_id'],$bank['cc_number'],$amount,$bank['gvp_provaut_password']);
		$expiredate=$bank['cc_expire_date_month'].$bank['cc_expire_date_year'];
		$xml_fields=array('mode'=>"PROD",
		'version'=>"v0.01",
		'prov_user_id'=>"PROVAUT",
		'hash'=>$hash,
		'user_id'=>$bank['gvp_user_name'],
		'terminal_id'=>$bank['gvp_terminal_id'],
		'merchant_id'=>$bank['gvp_merchant_id'],
		'customer_ip'=>$bank['customer_ip'],
		'email'=>"",
		'cardnumber'=>$bank['cc_number'],
		'expiredate'=>$expiredate,
		'cvv2'=>$bank['cc_cvv2'],
		'oid'=>$bank['order_id'],
		'type'=>"sales",
		'instalment'=>$instalment,
		'amount'=>$amount,
		'currency'=>"949",
		'url'=>$action,
		'bank_id'=>$bank['bank_id']
		);
		$xml_response=$this->xmlSend($xml_fields);
		$xml = simplexml_load_string($xml_response);
				$ReasonCode=(string)$xml->Transaction->Response->ReasonCode;
				$ResponseMessage=(string)$xml->Transaction->Response->Message;
				
				if($ReasonCode =="00" || $ResponseMessage === "Approved") {
					$response['result']=1;
					$response['message'].='Ödeme Başarılı<br/>';
					$response['message'].='AuthCode : '.(string)$xml->Transaction->Response->AuthCode[0].'<br/>';
					$response['message'].='Response : '.$ResponseMessage.'<br/>';
					$response['redirect']='success';
				} else {
					$response['result']=0;
					$response['message'].='Ödeme Başarısız.<br/>';
					$response['message'].='Response : '.$ResponseMessage.'<br/>';
					$response['message'].='ErrMsg : '.(string)$xml->Transaction->Response->SysErrMsg.'<br/>';
					$response['error']=(string)$xml->Transaction->Response->SysErrMsg;
				}
		//$response['form']=;
		return $response;
		
	}

	private function xmlSend($fields){

			$request = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
					<GVPSRequest>
					<Mode>".$fields['mode']."</Mode>
					<Version>".$fields['version']."</Version>
					<Terminal>
					<ProvUserID>".$fields['prov_user_id']."</ProvUserID>
					<HashData>".$fields['hash']."</HashData>
					<UserID>".$fields['user_id']."</UserID>
					<ID>".$fields['terminal_id']."</ID>
					<MerchantID>".$fields['merchant_id']."</MerchantID>
					</Terminal>
					<Customer>
					<IPAddress>".$fields['customer_ip']."</IPAddress>
					<EmailAddress>".$fields['email']."</EmailAddress>
					</Customer>
					<Card>
					<Number>".$fields['cardnumber']."</Number>
					<ExpireDate>".$fields['expiredate']."</ExpireDate>
					<CVV2>".$fields['cvv2']."</CVV2>
					</Card>
					<Order>
					<OrderID>".$fields['oid']."</OrderID>					
					<GroupID></GroupID>					
					<AddressList>
					<Address>
					<Type>S</Type>
					<Name></Name>
					<LastName></LastName>
					<Company></Company>
					<Text></Text>
					<District></District>
					<City></City>
					<PostalCode></PostalCode>
					<Country></Country>
					<PhoneNumber></PhoneNumber>
					</Address>
					</AddressList>
					</Order>
					<Transaction>
					<Type>".$fields['type']."</Type>
					<InstallmentCnt>".$fields['instalment']."</InstallmentCnt>
					<Amount>".$fields['amount']."</Amount>
					<CurrencyCode>".$fields['currency']."</CurrencyCode>
					<CardholderPresentCode>0</CardholderPresentCode>
					<MotoInd>N</MotoInd>
					<Description></Description>
					<OriginalRetrefNum></OriginalRetrefNum>
					</Transaction>
					</GVPSRequest>";
		// URL below is payment gateway's adress ( API Server), it is NOT 3D Gateway.		
		$url = $fields['url'];
				
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL,$url); 		// set url to post to
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);//prevent Poddle attack, have to set 0 to use TLS instead of SSL3 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); 		// times out after 90s
		curl_setopt($ch, CURLOPT_POSTFIELDS, "data=".$request); // add POST fields
				
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$result='<GVPSResponse><Transaction><Response><SysErrMsg>cUrl Error: '.curl_error($ch).'</SysErrMsg></Response></Transaction></GVPSResponse>';
		}
		
		curl_close($ch);
		return $result;
	}
}