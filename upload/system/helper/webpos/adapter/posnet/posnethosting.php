<?php
class posnetHosting {
	// non-3d hosting method for posnet
	public function methodResponse($bank){
		$response=array();
		$response['form']=$this->createForm($bank);
		//$response['redirect']=;
		//$response['error']=;
		//$response['form'];
		return $response;
		
	}
	
		private function createForm($bank) {
		$xid=substr("00000000000000000000".$bank['order_id'],-20);
		if($bank['instalment']!=0){
			$instalment=$bank['instalment'];
		} else {
			$instalment="00";
		}
			$inputs=array();
			$inputs=array('posnetID'=>$bank['posnet_posnet_id'],
			'mid'=>$bank['posnet_merchant_id'],
			'xid'=>$xid,
			'tranType'=>"Sale",
			'amount'=>(int)($bank['total']*100),
			'instalment'=>$instalment,
			'currencyCode'=>"TL",
			'merchantReturnSuccessURL'=>$bank['success_url'],
			'merchantReturnFailURL'=>$bank['fail_url'],
			'openANewWindow'=>"0",
			'bank_id'=>$bank['bank_id'],
			'oid'=>$bank['order_id'],
			'useJokerVadaa'=>"1");
			$action='';
			if ($bank['mode']=='live') {
				$action=$bank['posnet_3D_url'];
			} else if ($bank['mode']=='test') {
				$action=$bank['posnet_test_url'];
			}
			
			$form='<form id="webpos_form" name="webpos_form" method="post" action="'.$action.'">';
			foreach($inputs as $key=>$value){ 

				$form.='<input type="hidden" name="'.$key.'" value="'.$value.'" />';

			} 
			$form.='</form>';
			
		return $form;
			
		}
	
	public function bankResponse($bank_response,$bank){
		$response=array();
		$response['message']='';
		if (isset($bank_response['returncode']) && $bank_response['returncode'] == 1) { 
				$authCode= isset($bank_response['authCode'])?$bank_response['authCode']:"";
				$ykbrefno= isset($bank_response['ykbrefno'])?$bank_response['ykbrefno']:"";
				$response['result']=1;
				$response['message'].='Ödeme Başarılı<br/>';
				$response['message'].='AuthCode : '.$authCode.'<br/>';
				$response['message'].='ykbrefno : '.$ykbrefno.'<br/>';
				$response['message'].='Instalment : '.$bank['instalment'].'<br/>';
				$response['message'].='Amount : '.$bank['total'].'<br/>';
			} else {
				$response['result']=0;
				$response['message'].=$bank_response['errmsg'];
			}
		return $response;
	}
}