<?php
class ControllerPaymentWebpos extends Controller {
	public function index() {
		$this->load->language('payment/webpos');

		$data['text_credit_card'] = $this->language->get('text_credit_card');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_3d_hosting'] = $this->language->get('text_3d_hosting');

		$data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
		$data['entry_cc_number'] = $this->language->get('entry_cc_number');
		$data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
		$data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_back'] = $this->language->get('button_back');

		$data['months'] = array();

		for ($i = 1; $i <= 12; $i++) {
			$data['months'][] = array(
			'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
			'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$data['year_expire'] = array();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][] = array(
			'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
			'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
			);
		}
		$data['cc_types'] = array();
		//add supported cards VISA-MasterCard-Amex etc
		$data['cc_types'][]=array('text'=>'VISA','value'=>'1');//VISA
		$data['cc_types'][]=array('text'=>'MasterCard','value'=>'2');//MasterCard
		//$data['cc_types'][]=array('text'=>'AMEX','value'=>'3');//American Express
		$bank_id=$this->session->data['webpos_bank_id'];
		$bank=$this->getbank($bank_id);
		$data['payment_model']=$bank['model'];
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpos.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/webpos.tpl', $data);
		} else {
			return $this->load->view('default/template/payment/webpos.tpl', $data);
		}
	}
	public function helperload($helper) {
		$file = DIR_SYSTEM . 'helper/webpos/adapter/' . $helper . '.php';
		$class_only=explode('/',$helper);
		$class = preg_replace('/[^a-zA-Z0-9]/', '', $class_only[1]);
		if (file_exists($file)) {
			include_once($file);
			$this->registry->set('webpos_' . str_replace('/', '_', $class_only[1]), new $class($this->registry));
		} else {
			trigger_error('Error: Could not load webpos helper ' . $file . '!');
			exit();
		}
	}
	private function getbank($bank_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "webposbank WHERE bank_id = '" . (int)$bank_id . "'");
		
		return $query->row;
	}

	public function instalments() {
		$this->load->language('payment/webpos');
		$data['text_instalments']=$this->language->get('text_instalments');
		$data['text_instalment']=$this->language->get('text_instalment');
		$data['text_no_instalment']=$this->language->get('text_no_instalment');
		$data['webpos_other_id']=$this->config->get('webpos_other_id');
		$this->load->model('checkout/order');
		$order_total = $this->cart->getTotal();
				$webpos_single_ratio=floatval($this->config->get('webpostotal_single_ratio'));
		//
			if ($webpos_single_ratio>=0){
				$webpos_single_title=$this->language->get('text_single_positive').'(%'.$webpos_single_ratio.')';
			} else if($webpos_single_ratio<0){
				$webpos_single_title=$this->language->get('text_single_negative').'(%'.$webpos_single_ratio.')';
			}
			$webpos_total=$order_total+($order_total*$webpos_single_ratio/100);
		//
		$data['single_order_total']=$this->currency->format($webpos_total, $this->session->data['currency'], false, true);
		$data['webpos_single_title']=$webpos_single_title;
		
		$data['banks']=$this->config->get('webpos_banks_info');
		$new_banks=array();
		foreach($data['banks'] as $bank){
			if ($bank['status']!=0){
				$new_banks[$bank['bank_id']]=$bank;
				if(!empty($bank['instalment']) || $bank['instalment']!=''){
					$instalments=array();
					$instalments=explode(';',$bank['instalment']);
					foreach($instalments as $instalment) {
						$instalment_array=explode('=',$instalment);
						$instalment_count=$instalment_array[0];
						$instalment_ratio=$instalment_array[1];
						$instalment_total=$order_total+($order_total*$instalment_ratio)/100;
						if($instalment_count!=0){
							$instalment_price=$instalment_total/$instalment_count;
						} else {
							$instalment_price=$order_total;
						}
						//$this->session->data['currency'];
						$instalment_total=$this->currency->format($instalment_total, $this->session->data['currency'], false, true);
						$instalment_price=$this->currency->format($instalment_price, $this->session->data['currency'], false, true);
						$new_banks[$bank['bank_id']]['instalments'][]=array('count'=>$instalment_count,
						'ratio'=>$instalment_ratio,
						'total'=>$instalment_total,
						'price'=>$instalment_price);
					}
				}
			}
		}
		unset($data['banks']);
		$data['banks']=$new_banks;
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpos_instalment.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/webpos_instalment.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/payment/webpos_instalment.tpl', $data));
		}
	}
	
	public function send() {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$banks=$this->config->get('webpos_banks_info');
		$bank_id=$this->session->data['webpos_bank_id'];
		
		$webpos_bank=array();
		$webpos_class='';
		foreach ($banks as $bank) {
			if($bank['bank_id']==$bank_id){
				$webpos_bank=$bank;
				$webpos_class=$bank['method'].'/'.$bank['method'].$bank['model'];
			}
		}
		//load method.model class
		$this->helperload($webpos_class);
		
		if(isset($this->session->data['instalment'])) {
			$instalment_data=explode('_',$this->session->data['instalment']);
			$instalment_array=explode('x',$instalment_data[1]);
			$instalment=$instalment_array[0];
		} else {
			$instalment=0;
		}
		if($webpos_bank['model']=="3d_Hosting" || $webpos_bank['model']=="Hosting"){
			//
		} else {
		$webpos_bank['cc_owner']=$this->request->post['cc_owner'];
		$webpos_bank['cc_number']=$this->request->post['cc_number'];
		$webpos_bank['cc_cvv2']=$this->request->post['cc_cvv2'];
		$webpos_bank['cc_expire_date_month']=$this->request->post['cc_expire_date_month'];
		$webpos_bank['cc_expire_date_year']=$this->request->post['cc_expire_date_year'];
		$webpos_bank['cc_type']=$this->request->post['cc_type'];
		}
		$webpos_bank['customer_ip']=$this->request->server['REMOTE_ADDR'];
				
		$webpos_bank['instalment']=$instalment;
		
		$webpos_bank['success_url']=$this->url->link('payment/webpos/callback', '', 'SSL'); //bank will return here if payment successfully finishes;
		$webpos_bank['fail_url']=$this->url->link('payment/webpos/callback', '', 'SSL'); //bank will return here if payment fails;
		
		$webpos_bank['order_id']=$this->session->data['order_id']; //unique order id 
		$webpos_bank['total']=$this->currency->format($order_info['total'], $order_info['currency_code'], false, false);//total order amount
		$webpos_bank['mode']=$this->config->get('webpos_mode');
		
		$method_response=array();
		$method_response=$this->{'webpos_'.$webpos_bank['method'].$webpos_bank['model']}->methodResponse($webpos_bank);

		//create object to use as json
		$json = array();

		if (isset($method_response['form'])) {
			$json['form']= $method_response['form'];
		} else if (isset($method_response['redirect'])){
			$message=$method_response['message'];
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('webpos_order_status_id'), $message, false);

			$json['redirect'] = $this->url->link('checkout/success', '', 'SSL');
			unset($this->session->data['instalment']);
			unset($this->session->data['webpos_bank_id']);
		} else if(isset($method_response['error'])) {
			$json['error'] = $method_response['error'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function callback(){
		$this->load->language('payment/webpos');

		$data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		if (!$this->request->server['HTTPS']) {
			$data['base'] = $this->config->get('config_url');
		} else {
			$data['base'] = $this->config->get('config_ssl');
		}

		$data['language'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		$data['text_response'] = $this->language->get('text_response');
		$data['text_success'] = $this->language->get('text_success');
		$data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
		$data['text_failure'] = $this->language->get('text_failure');
		$data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));
		
		$this->load->model('checkout/order');
		
		$bank_id=$this->request->post['bank_id'];
		$order_id=$this->request->post['oid'];
		
		$bank_response=$this->request->post;
		$banks=$this->config->get('webpos_banks_info');
		//$bank_id=$this->session->data['webpos_bank_id'];
		foreach ($banks as $bank) {
			if($bank['bank_id']==$bank_id){
				$webpos_bank=$bank;
				$webpos_class=$bank['method'].'/'.$bank['method'].$bank['model'];
			}
		}
		//load method.model class
		$this->helperload($webpos_class);
		
		$method_response=array();
		$method_response=$this->{'webpos_'.$webpos_bank['method'].$webpos_bank['model']}->bankResponse($bank_response,$webpos_bank);
		if ($method_response['result']==1){
			$message=$method_response['message'].$webpos_bank['name'];
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('webpos_order_status_id'), $message, false);
			unset($this->session->data['order_id']);
			unset($this->session->data['instalment']);
			unset($this->session->data['webpos_bank_id']);
			//standard opencart redirect
			$data['continue'] = $this->url->link('checkout/success');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpos_success.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/webpos_success.tpl', $data));
			} else {
				$this->response->setOutput($this->load->view('default/template/payment/webpos_success.tpl', $data));
			}
			//

		} else {
			unset($this->session->data['order_id']);
			unset($this->session->data['instalment']);
			unset($this->session->data['webpos_bank_id']);
			//standard opencart redirect
			$data['continue'] = $this->url->link('checkout/checkout');
			$data['message']=$method_response['message'];
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpos_failure.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/webpos_failure.tpl', $data));
			} else {
				$this->response->setOutput($this->load->view('default/template/payment/webpos_failure.tpl', $data));
			}
			//
		}
		

	}
}