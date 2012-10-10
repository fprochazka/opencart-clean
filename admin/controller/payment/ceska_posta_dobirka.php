<?php
class ControllerPaymentCeskaPostaDobirka extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/ceska_posta_dobirka');

		$this->document->title = $this->language->get('heading_title');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('ceska_posta_dobirka', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['entry_dobirka'] = $this->language->get('entry_dobirka');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_none'] = $this->language->get('text_none');

		$this->data['entry_tax'] = $this->language->get('entry_tax');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['entry_price'] = $this->language->get('entry_dobirka');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=payment/ceska_posta_dobirka&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/ceska_posta_dobirka&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		if (isset($this->request->post['ceska_posta_dobirka_ceny'])) {
			$this->data['ceska_posta_dobirka_ceny'] = $this->request->post['ceska_posta_dobirka_ceny'];
		} elseif ($this->config->get('ceska_posta_dobirka_ceny')) {
			$this->data['ceska_posta_dobirka_ceny'] = $this->config->get('ceska_posta_dobirka_ceny');
		} else {
			$this->data['ceska_posta_dobirka_ceny'] = '5000:34,50000:45,60000:51,70000:57,80000:63,90000:69,100000:75';
    	}

		if (isset($this->request->post['ceska_posta_dobirka_order_status_id'])) {
			$this->data['ceska_posta_dobirka_order_status_id'] = $this->request->post['ceska_posta_dobirka_order_status_id'];
		} else {
			$this->data['ceska_posta_dobirka_order_status_id'] = $this->config->get('ceska_posta_dobirka_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['ceska_posta_dobirka_tax_class_id'])) {
			$this->data['ceska_posta_dobirka_tax_class_id'] = $this->request->post['ceska_posta_dobirka_tax_class_id'];
		} else {
			$this->data['ceska_posta_dobirka_tax_class_id'] = $this->config->get('ceska_posta_dobirka_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['ceska_posta_dobirka_geo_zone_id'])) {
			$this->data['ceska_posta_dobirka_geo_zone_id'] = $this->request->post['ceska_posta_dobirka_geo_zone_id'];
		} else {
			$this->data['ceska_posta_dobirka_geo_zone_id'] = $this->config->get('ceska_posta_dobirka_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['ceska_posta_dobirka_status'])) {
			$this->data['ceska_posta_dobirka_status'] = $this->request->post['ceska_posta_dobirka_status'];
		} else {
			$this->data['ceska_posta_dobirka_status'] = $this->config->get('ceska_posta_dobirka_status');
		}

		if (isset($this->request->post['ceska_posta_dobirka_sort_order'])) {
			$this->data['ceska_posta_dobirka_sort_order'] = $this->request->post['ceska_posta_dobirka_sort_order'];
		} else {
			$this->data['ceska_posta_dobirka_sort_order'] = $this->config->get('ceska_posta_dobirka_sort_order');
		}

		$this->template = 'payment/ceska_posta_dobirka.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ceska_posta_dobirka')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
