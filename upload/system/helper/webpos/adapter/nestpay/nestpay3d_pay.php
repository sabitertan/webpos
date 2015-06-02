<?php
class nestpay3DPay {
	
	private function createHash($clientId,$storekey,$okUrl,$failUrl,$oid,$amount,$rnd,$transactionType,$installment) {
		$hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $transactionType . $installment . $rnd . $storekey; 
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
		$transactionType="Auth";
		$hash=$this->createHash($clientId,$storekey,$okUrl,$failUrl,$oid,$amount,$rnd,$transactionType,$taksit);
		
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
		'islemtipi'=>$transactionType,
		'taksit'=>$taksit,
		'storetype'=>"3d_pay",
		'lang'=>"tr",
		'currency'=>"949",
		'firmaadi'=>$bank['nestpay_3D_storename'],
		'refreshtime'=>"10",
		'Fismi'=>$bank['order_info']['firstname'] . ' ' . $bank['order_info']['lastname'],
		'Fadres'=>$bank['order_info']['shipping_address_1'],
		'Fadres2'=>$bank['order_info']['shipping_address_2'],
		'Fil'=>$bank['order_info']['shipping_zone'],
		'Filce'=>$bank['order_info']['shipping_city'],
		'fulkekod'=>$bank['order_info']['shipping_iso_code_2'],
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
				
				$Response=isset($bank_response['Response'])?$bank_response['Response']:'';
				$OrderId=isset($bank_response['OrderId'])?$bank_response['OrderId']:'';
				$AuthCode=isset($bank_response['AuthCode'])?$bank_response['AuthCode']:'';
				$ProcReturnCode=isset($bank_response['ProcReturnCode'])?$bank_response['ProcReturnCode']:'';
				$ErrMsg=isset($bank_response['ErrMsg'])?$bank_response['ErrMsg']:'';
				$HostRefNum=isset($bank_response['HostRefNum'])?$bank_response['HostRefNum']:'';
				$TransId=isset($bank_response['TransId'])?$bank_response['TransId']:'';
				
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
}