<?php
class gvp3dModel {
	private function createHash($terminal_id,$oid,$amount,$okUrl,$failUrl,$type,$instalment,$storekey,$provaut_password) {
		$secData=strtoupper(sha1($provaut_password."0".$terminal_id));
		$hashstr = $terminal_id . $oid . $amount . $okUrl . $failUrl . $type . $instalment . $storekey . $secData;
		$hash = strtoupper(sha1($hashstr));
		return $hash;
	}
	
	private function createForm($bank) {
		if($bank['instalment']!=0){
			$instalment=$bank['instalment'];
		} else {
			$instalment="";
		}
		$amount=(int)($bank['total']*100);
		$hash=$this->createHash($bank['gvp_terminal_id'],$bank['order_id'],$amount,$bank['success_url'],$bank['fail_url'],"sales",$instalment,$bank['gvp_3D_storekey'],$bank['gvp_provaut_password']);
		
		$inputs=array();
		$inputs=array('secure3dsecuritylevel'=>"3D",
		'cardnumber'=>$bank['cc_number'],
		'cardexpiredatemonth'=>$bank['cc_expire_date_month'],
		'cardexpiredateyear'=>$bank['cc_expire_date_year'],
		'cardcvv2'=>$bank['cc_cvv2'],
		'mode'=>"PROD",
		'apiversion'=>"v0.01",
		'terminalprovuserid'=>"PROVAUT",
		'terminaluserid'=>$bank['gvp_user_name'],
		'terminalmerchantid'=>$bank['gvp_merchant_id'],
		'txntype'=>"sales",
		'txnamount'=>$amount,
		'txncurrencycode'=>"949",
		'txninstallmentcount'=>$instalment,
		'orderid'=>$bank['order_id'],
		'terminalid'=>$bank['gvp_terminal_id'],
		'successurl'=>$bank['success_url'],
		'errorurl'=>$bank['fail_url'],
		'customeripaddress'=>$bank['customer_ip'],
		'customeremailaddress'=>"",
		'secure3dhash'=>$hash,
		'bank_id'=>$bank['bank_id'],
		'oid'=>$bank['order_id']
		);
		$action='';
		if ($bank['mode']=='live') {
			$action=$bank['gvp_3D_url'];
		} else if ($bank['mode']=='test') {
			$action=$bank['gvp_test_url'];
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
		
		
			$mdStatus=$bank_response['mdstatus'];// if mdstatus 1,2,3,4 then 3D authentication is successful, if mdstatus 5,6,7,8,9,0 then 3D authentication is FAILED
			$mdArray=array('1','2','3','4');
			if (in_array($mdStatus,$mdArray)){
				$response['message'].='3D Onayı Başarılı.<br/>';
				//field
				//hash
				$secData=strtoupper(sha1($bank['gvp_provaut_password']."0".$bank_response['clientid']));
				$hashstr = $bank_response['orderid'] . $bank_response['clientid'] . $bank_response['txnamount'] . $secData;
				$hash = strtoupper(sha1($hashstr));
				//
				$xml_fields=array('mode'=>$bank_response['mode'],
				'version'=>$bank_response['apiversion'],
				'terminal_id'=>$bank_response['clientid'],
				'prov_user_id'=>$bank_response['terminalprovuserid'],
				'hash'=>$hash,
				'user_id'=>$bank_response['terminaluserid'],
				'merchant_id'=>$bank_response['terminalmerchantid'],
				'customer_ip'=>$bank_response['customeripaddress'],
				'email'=>$bank_response['customeremailaddress'],
				'oid'=>$bank_response['orderid'],
				'type'=>$bank_response['txntype'],
				'instalment'=>$bank_response['txninstallmentcount'],
				'amount'=>$bank_response['txnamount'],
				'currency'=>$bank_response['txncurrencycode'],
				'auth_code'=>$bank_response['cavv'],
				'sec_level'=>$bank_response['eci'],
				'txn_id'=>$bank_response['xid'],
				'md'=>$bank_response['md'],
				'url'=>$bank['gvp_classic_url']
				);
				//field
				$xml_response=$this->xmlSend($xml_fields);
				$xml = simplexml_load_string($xml_response);
				
				$ReasonCode=(string)$xml->Transaction->Response->ReasonCode;
				$Response=(string)$xml->Transaction->Response->Message;
				
				if($ReasonCode =="00" || $Response === "Approved") {
					$response['result']=1;
					$response['message'].='Ödeme Başarılı<br/>';
					$response['message'].='AuthCode : '.(string)$xml->Transaction->Response->AuthCode.'<br/>';
					$response['message'].='Response : '.$Response.'<br/>';
				} else {
					$response['result']=0;
					$response['message'].='Ödeme Başarısız.<br/>';
					$response['message'].='Response : '.$Response.'<br/>';
					$response['message'].='ErrMsg : '.(string)$xml->Transaction->Response->SysErrMsg.'<br/>';
					$response['message'].='ErrCode : '.(string)$xml->Transaction->Response->Code.'<br/>';
				}
			
			} else {
				$response['result']=0;
				$response['message'].='3D doğrulama başarısız<br/>';
				$response['message'].=$bank_response['mderrormessage'];
				
			}
		
		//print_r($response);
		return $response;
	}
	private function xmlSend($fields){

			$request = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
					<GVPSRequest>
					<Mode>".$fields['mode']."</Mode>
					<Version>".$fields['version']."</Version>
					<ChannelCode></ChannelCode>
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
					<Number></Number>
					<ExpireDate></ExpireDate>
					<CVV2></CVV2>
					</Card>
					<Order>
					<OrderID>".$fields['oid']."</OrderID>					
					<GroupID></GroupID>					
					<AddressList>
					<Address>
					<Type>B</Type>
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
					<CardholderPresentCode>13</CardholderPresentCode>
					<MotoInd>N</MotoInd>
					<Secure3D>
					<AuthenticationCode>".$fields['auth_code']."</AuthenticationCode>
					<SecurityLevel>".$fields['sec_level']."</SecurityLevel>
					<TxnID>".$fields['txn_id']."</TxnID>
					<Md>".$fields['md']."</Md>
					</Secure3D>
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