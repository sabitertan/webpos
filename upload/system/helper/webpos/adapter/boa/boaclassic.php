<?php
class boaClassic {
	public function methodResponse($bank){
		$response=array();
		$response['message']='';
		$action='';
		if ($bank['mode']=='live') {
			$action=$bank['boa_classic_url'];
		} else if ($bank['mode']=='test') {
			$action=$bank['boa_test_url'];
		}
		$amount=$bank['total']*100;
		$HashedPassword = base64_encode(sha1($bank['boa_classic_password'],"ISO-8859-9"));
		$hashstr=$bank['boa_merchant_id'].$bank['order_id'].$amount.$bank['success_url'].$bank['fail_url'].$bank['boa_classic_name'].$HashedPassword;
		$hash= base64_encode(sha1($hashstr,"ISO-8859-9"));
		if($bank['cc_type']==1){
			$cardType="VISA";
		} else if($bank['cc_type']==2){
			$cardType="MasterCard";
		}
		
		$xml_fields=array('okUrl'=>$bank['success_url'],
		'failUrl'=>$bank['fail_url'],
		'hash'=>$hash,
		'merchant_id'=>$bank['boa_merchant_id'],
		'customer_id'=>$bank['boa_customer_id'],
		'username' => $bank['boa_classic_name'],
		'password' => $bank['boa_classic_password'],
		'cardnumber'=>$bank['cc_number'],
		'expireYear'=>$bank['cc_expire_date_year'],
		'expireMonth'=>$bank['cc_expire_date_month'],
		'cardcvv2' => $bank['cc_cvv2'],
		'cardname'=>$bank['cc_owner'],
		'cardType'=>$cardType,
		'type' => 'Sale',
		'instalment'=>$bank['instalment'],
		'amount'=>$amount,
		'displayAmount'=>$bank['total'],
		'oid' => $bank['order_id'],
		'securityLevel'=>"1",
		'url'=>$action
		);
		//field
		$xml_response=$this->xmlSend($xml_fields);
		$form=explode('form',$xml_response);
		$form='<form '.$form[1].'form>';
		$form=str_replace('name="downloadForm"','id="webpos_form" name="webpos_form"',$form);

		//$response['error']=;
		//$response['message']=;
			
			$response['form']=$form;
		return $response;
		
	}
	public function bankResponse($bank_response,$bank){
				
		$ResponseMessage=isset($bank_response['ResponseMessage'])?$bank_response['ResponseMessage']:'';
		$OrderId=isset($bank_response['MerchantOrderID'])?$bank_response['MerchantOrderID']:'';
		$ResponseCode=isset($bank_response['ResponseCode'])?$bank_response['ResponseCode']:'';
	
		if($ResponseCode =="00") {
			$response['result']=1;
			$response['message']='Ödeme Başarılı<br/>';
			$response['message'].='ResponseMessage : '.$ResponseMessage.'<br/>';
			$response['message'].='ResponseCode : '.$ResponseCode.'<br/>';
			$response['message'].='MerchantOrderID : '.$OrderId.'<br/>';
		} else {
			$response['result']=0;
			$response['message']=$ResponseMessage;
		}
		return $response;
	}

	private function xmlSend($fields){

		$request= '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.
				'<APIVersion>1.0.0</APIVersion>'.
				'<OkUrl>'.$fields['okUrl'].'</OkUrl>'.
				'<FailUrl>'.$fields['failUrl'].'</FailUrl>'.
				'<HashData>'.$fields['hash'].'</HashData>'.
				'<MerchantId>'.$fields['merchant_id'].'</MerchantId>'.
				'<CustomerId>'.$fields['customer_id'].'</CustomerId>'.
				'<UserName>'.$fields['username'].'</UserName>'.
				'<CardNumber>'.$fields['cardnumber'].'</CardNumber>'.
				'<CardExpireDateYear>'.$fields['expireYear'].'</CardExpireDateYear>'.
				'<CardExpireDateMonth>'.$fields['expireMonth'].'</CardExpireDateMonth>'.
				'<CardCVV2>'.$fields['cardcvv2'].'</CardCVV2>'.
				'<CardHolderName>'.$fields['cardname'].'</CardHolderName>'.
				'<CardType>'.$fields['cardType'].'</CardType>'.
				'<BatchID>0</BatchID>'.
				'<TransactionType>'.$fields['type'].'</TransactionType>'.
				'<InstallmentCount>'.$fields['instalment'].'</InstallmentCount>'.
				'<Amount>'.$fields['amount'].'</Amount>'.
				'<DisplayAmount>'.$fields['displayAmount'].'</DisplayAmount>'.
				'<CurrencyCode>0949</CurrencyCode>'.
				'<MerchantOrderId>'.$fields['oid'].'</MerchantOrderId>'.
				'<TransactionSecurity>'.$fields['securityLevel'].'</TransactionSecurity>'.
				'</KuveytTurkVPosMessage>';
		// URL below is payment gateway's adress ( API Server), it is NOT 3D Gateway.		
		$url = $fields['url'];
		
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL,$url); 		// set url to post to
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);//prevent Poddle attack, have to set 0 to use TLS instead of SSL3 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); 		// times out after 90s
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // add POST fields
		
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$result='<KuveytTurkVPosResponse><ResponseMessage>cUrl Error: '.curl_error($ch).'</ResponseMessage></KuveytTurkVPosResponse>';
		}
		/* Response XML
			<KuveytTurkVPosResponse>
				<ResponseCode></ResponseCode>
				<ResponseMessage></ResponseMessage>
				<MerchantOrderID></MerchantOrderID>
			</KuveytTurkVPosResponse>
		*/
		
		curl_close($ch);
		/*if (strpos( $result, "<KuveytTurkVPosResponse>" )!==true){
			$result='<KuveytTurkVPosResponse><ResponseMessage>XML Error: '.$result.'</ResponseMessage></KuveytTurkVPosResponse>';
		}*/
		
		return $result;
	}
}