<?php
class ControllerPaymentWebPos extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/webpos');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('webpos', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_test'] = $this->language->get('text_test');
		$data['text_live'] = $this->language->get('text_live');
		$data['text_debug'] = $this->language->get('text_debug');
		
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_add'] = $this->language->get('tab_add');
		$data['tab_add_url'] = $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'], 'SSL');

		$data['entry_mode'] = $this->language->get('entry_mode');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_instalment'] = $this->language->get('entry_instalment');
		$data['entry_other'] = $this->language->get('entry_other');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['login'])) {
			$data['error_login'] = $this->error['login'];
		} else {
			$data['error_login'] = '';
		}

		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_payment'),
		'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('payment/webpos', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = HTTPS_SERVER . 'index.php?route=payment/webpos&token=' . $this->session->data['token'];

		$data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];
		
		//@TODO: bring banks info here 29.12.2014 
		//entries
		//EST
		$data['entry_nestpay_client_id']=  $this->language->get('entry_nestpay_client_id');
		$data['entry_nestpay_classic_name']=  $this->language->get('entry_nestpay_classic_name');
		$data['entry_nestpay_classic_password']=   $this->language->get('entry_nestpay_classic_password');
		$data['entry_nestpay_3D_storekey']=   $this->language->get('entry_nestpay_3D_storekey');
		$data['entry_nestpay_3D_storename']=   $this->language->get('entry_nestpay_3D_storename');
		$data['entry_nestpay_test_url']=   $this->language->get('entry_nestpay_test_url');
		$data['entry_nestpay_classic_url']=  $this->language->get('entry_nestpay_classic_url');
		$data['entry_nestpay_3D_url']=   $this->language->get('entry_nestpay_3D_url');

		
		//GVP
		$data['entry_gvp_terminal_id']=   $this->language->get('entry_gvp_terminal_id');
		$data['entry_gvp_merchant_id']=   $this->language->get('entry_gvp_merchant_id');
		$data['entry_gvp_user_name']=   $this->language->get('entry_gvp_user_name');
		$data['entry_gvp_provaut_password']=   $this->language->get('entry_gvp_provaut_password');
		$data['entry_gvp_3D_storename']=   $this->language->get('entry_gvp_3D_storename');
		$data['entry_gvp_3D_storekey']=   $this->language->get('entry_gvp_3D_storekey');
		$data['entry_gvp_test_url']=   $this->language->get('entry_gvp_test_url');
		$data['entry_gvp_classic_url']=   $this->language->get('entry_gvp_classic_url');
		$data['entry_gvp_3D_url']=   $this->language->get('entry_gvp_3D_url');
		
		//POSNET		
		$data['entry_posnet_posnet_id'] =   $this->language->get('entry_posnet_posnet_id');
		$data['entry_posnet_terminal_id']=   $this->language->get('entry_posnet_terminal_id');
		$data['entry_posnet_merchant_id']=   $this->language->get('posnet_merchant_id');
		$data['entry_posnet_user_name']=   $this->language->get('entry_posnet_user_name');
		$data['entry_posnet_user_password']=   $this->language->get('entry_posnet_user_password');
		$data['entry_posnet_test_url']=   $this->language->get('entry_posnet_test_url');
		$data['entry_posnet_classic_url']=   $this->language->get('entry_posnet_classic_url');
		$data['entry_posnet_3D_url']=   $this->language->get('entry_posnet_3D_url');
		//
		//BOA
		$data['entry_boa_merchant_id']        = $this->language->get('entry_boa_merchant_id');
		$data['entry_boa_customer_id']        = $this->language->get('entry_boa_customer_id');
		$data['entry_boa_classic_name']     = $this->language->get('entry_boa_classic_name');
		$data['entry_boa_classic_password']         = $this->language->get('entry_boa_classic_password');
		$data['entry_boa_test_url']         = $this->language->get('entry_boa_test_url');
		$data['entry_boa_classic_url']      = $this->language->get('entry_boa_classic_url');
		$data['entry_boa_3D_url']         = $this->language->get('entry_boa_3D_url');
		//
		//PAYU
		$data['entry_payu_merchant_id']        = $this->language->get('entry_payu_merchant_id');
		$data['entry_payu_secret_key']        = $this->language->get('entry_payu_secret_key');
		$data['entry_payu_alu_url']     = $this->language->get('entry_payu_alu_url');
		//
		$this->load->model('extension/webposbuilder');
		$banks=$this->model_extension_webposbuilder->getbanks(array('sort'=>'bank_id'));
		//fix key sort order
		$new_banks=array();
		foreach($banks as $bank) {
			$new_banks[$bank['bank_id']]=$bank;
		}
		unset($banks);
		$banks=$new_banks;
		//
		$data['banks_info']=array();
		if (isset($this->request->post['webpos_banks_info'])) {
			$data['banks_info'] = $this->request->post['webpos_banks_info'];
		} else {
			$data['banks_info'] = $this->config->get('webpos_banks_info');
		}
		$this->load->model('tool/image');
		foreach($banks as $bank) {
			if (!empty($bank['image'])){
			$image=$this->model_tool_image->resize($bank['image'], 120, 40);
			} else {
				$image='';
			}
			$bank_id=$bank['bank_id'];
			$banks[$bank_id]['entries']=array();
			$banks[$bank_id]['image']=$image;
			$position=strlen($bank['method']);
			$entries=array();
			$entries=$this->getMethodEntries($data,$bank['method'],$position);
			foreach($entries as $entry) {
				if(isset($data['banks_info'][$bank_id][$entry])) {
					$banks[$bank_id]['entries'][$entry] = $data['banks_info'][$bank_id][$entry];
				} else {
					$banks[$bank_id]['entries'][$entry] = '';
				}
			}
			if(isset($data['banks_info'][$bank_id]['instalment'])) {
				$banks[$bank_id]['entries']['instalment'] = $data['banks_info'][$bank_id]['instalment'];
			} else {
				$banks[$bank_id]['entries']['instalment'] = '';
			}
			
		}
		$data['banks'] = $banks;
		//var_dump($banks);
		//
		if (isset($this->request->post['webpos_mode'])) {
			$data['webpos_mode'] = $this->request->post['webpos_mode'];
		} else {
			$data['webpos_mode'] = $this->config->get('webpos_mode');
		}
		
		if (isset($this->request->post['webpos_other_id'])) {
			$data['webpos_other_id'] = $this->request->post['webpos_other_id'];
		} else {
			$data['webpos_other_id'] = $this->config->get('webpos_other_id');
		}

		if (isset($this->request->post['webpos_order_status_id'])) {
			$data['webpos_order_status_id'] = $this->request->post['webpos_order_status_id'];
		} else {
			$data['webpos_order_status_id'] = $this->config->get('webpos_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['webpos_geo_zone_id'])) {
			$data['webpos_geo_zone_id'] = $this->request->post['webpos_geo_zone_id'];
		} else {
			$data['webpos_geo_zone_id'] = $this->config->get('webpos_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['webpos_status'])) {
			$data['webpos_status'] = $this->request->post['webpos_status'];
		} else {
			$data['webpos_status'] = $this->config->get('webpos_status');
		}

		if (isset($this->request->post['webpos_total'])) {
			$data['webpos_total'] = $this->request->post['webpos_total'];
		} else {
			$data['webpos_total'] = $this->config->get('webpos_total');
		}

		if (isset($this->request->post['webpos_sort_order'])) {
			$data['webpos_sort_order'] = $this->request->post['webpos_sort_order'];
		} else {
			$data['webpos_sort_order'] = $this->config->get('webpos_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/webpos.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/webpos')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	public function getMethodEntries($data_array, $method, $position) {
		$entries=array();
		$position=6+$position;
		foreach ($data_array as $key => $value) {
			if (substr($key, 0, $position) == "entry_".$method) {
				$keywords=explode("_",$key);
				$entries[]=$keywords[1].'_'.$keywords[2].'_'.$keywords[3];
			}
		}
		return $entries;
	}

	public function install() {
		if (!$this->user->hasPermission('modify', 'payment/webpos')) {
			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			$this->load->model('payment/webpos');

			$this->model_payment_webpos->install();

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function uninstall() {
		if (!$this->user->hasPermission('modify', 'payment/webpos')) {
			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			$this->load->model('payment/webpos');

			$this->model_payment_webpos->uninstall();

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}
}