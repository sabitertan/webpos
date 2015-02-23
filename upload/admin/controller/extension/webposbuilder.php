<?php 
class ControllerExtensionWebposBuilder extends Controller {
	private $error = array();
 
	public function index() {
		$this->load->language('extension/webposbuilder');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/webposbuilder');
		
		$this->getList();
	}

	public function add() {
		$this->load->language('extension/webposbuilder');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/webposbuilder');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_webposbuilder->addbank($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/webposbuilder');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/webposbuilder');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_webposbuilder->editbank($this->request->get['bank_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
					
			$this->response->redirect($this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}
 
	public function delete() {
		$this->load->language('extension/webposbuilder');
 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/webposbuilder');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $bank_id) {
				$this->model_extension_webposbuilder->deletebank($bank_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
			
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		$data['add'] = $this->url->link('extension/webposbuilder/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('extension/webposbuilder/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		 
		$data['banks'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$bank_total = $this->model_extension_webposbuilder->getTotalbanks();
		
		$results = $this->model_extension_webposbuilder->getbanks($filter_data);
		$this->load->model('tool/image');
		foreach ($results as $result) {
			if (!empty($result['image'])){
			$image=$this->model_tool_image->resize($result['image'], 120, 40);
			} else {
				$image='';
			}
			$data['banks'][] = array(
				'bank_id' => $result['bank_id'],
				'name'      => $result['name'],	
				'image'      => $image,	
				'method'      => $result['method'],	
				'model'      => $result['model'],	
				'short'      => $result['short'],	
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link('extension/webposbuilder/edit', 'token=' . $this->session->data['token'] . '&bank_id=' . $result['bank_id'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		
		$data['column_name'] = $this->language->get('column_name');
		$data['column_image'] = $this->language->get('column_image');
		$data['column_method'] = $this->language->get('column_method');
		$data['column_model'] = $this->language->get('column_model');
		$data['column_short'] = $this->language->get('column_short');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');	

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
 
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
				if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['sort_name'] = $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$data['sort_status'] = $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $bank_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->url = $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($bank_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($bank_total - $this->config->get('config_limit_admin'))) ? $bank_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $bank_total, ceil($bank_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/webposbuilder_list.tpl', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_form'] = !isset($this->request->get['bank_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_image_manager'] = $this->language->get('text_image_manager');
 		$data['text_browse'] = $this->language->get('text_browse');
		$data['text_clear'] = $this->language->get('text_clear');			
		$data['text_link_help'] = $this->language->get('text_link_help');		
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_method'] = $this->language->get('entry_method');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_short'] = $this->language->get('entry_short');
		$data['entry_image'] = $this->language->get('entry_image');	
		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

 		if (isset($this->error['method'])) {
			$data['error_method'] = $this->error['method'];
		} else {
			$data['error_method'] = '';
		}
 		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}
 		if (isset($this->error['short'])) {
			$data['error_short'] = $this->error['short'];
		} else {
			$data['error_short'] = '';
		}			
						
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
						
		if (!isset($this->request->get['bank_id'])) { 
			$data['action'] = $this->url->link('extension/webposbuilder/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/webposbuilder/edit', 'token=' . $this->session->data['token'] . '&bank_id=' . $this->request->get['bank_id'] . $url, 'SSL');
		}
		
		$data['cancel'] = $this->url->link('extension/webposbuilder', 'token=' . $this->session->data['token'] . $url, 'SSL');
		
		if (isset($this->request->get['bank_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$bank_info = $this->model_extension_webposbuilder->getbank($this->request->get['bank_id']);
		}
		
		$data['token'] = $this->session->data['token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($bank_info)) {
			$data['name'] = $bank_info['name'];
		} else {
			$data['name'] = '';
		}
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($bank_info)) {
			$data['image'] = $bank_info['image'];
		} else {
			$data['image'] = '';
		}
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($bank_info)) {
			$data['status'] = $bank_info['status'];
		} else {
			$data['status'] = true;
		}
		if (isset($this->request->post['method'])) {
			$data['method'] = $this->request->post['method'];
		} elseif (!empty($bank_info)) {
			$data['method'] = $bank_info['method'];
		} else {
			$data['method'] = '';
		}
		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($bank_info)) {
			$data['model'] = $bank_info['model'];
		} else {
			$data['model'] = '';
		}
		if (isset($this->request->post['short'])) {
			$data['short'] = $this->request->post['short'];
		} elseif (!empty($bank_info)) {
			$data['short'] = $bank_info['short'];
		} else {
			$data['short'] = '';
		}
	
		$this->load->model('tool/image');
		//thumb
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 120, 40);
		} elseif (!empty($bank_info) && is_file(DIR_IMAGE . $bank_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($bank_info['image'], 120, 40);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('catalog/webpos/webpos.png', 120, 40);
		}
		//
	
		$data['placeholder'] = $this->model_tool_image->resize('catalog/webpos/webpos.png', 120, 40);
	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/webposbuilder_form.tpl', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/webposbuilder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if (empty($this->request->post['method'])) {
			$this->error['method'] = $this->language->get('error_method');
		}
		if (empty($this->request->post['model'])) {
			$this->error['model'] = $this->language->get('error_model');
		}
		$short_check=$this->checkShortName($this->request->post['short']);
		if (!empty($this->request->post['short']) && !empty($short_check)) {
			$this->error['short'] = $this->language->get('error_short');
		}
		
		return !$this->error;
	}
	protected function checkShortName($short) {
	$this->load->model('extension/webposbuilder');
	$result = $this->model_extension_webposbuilder->checkShortName($short);
	return $result;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/webposbuilder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	
		return !$this->error;
	}
}