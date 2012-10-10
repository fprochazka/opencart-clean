<?php
class ControllerShippingCeskaPosta extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shipping/ceska_posta');

		$this->document->title = $this->language->get('heading_title');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('ceska_posta', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/shipping&token=' . $this->session->data['token']);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_sluzby'] = $this->language->get('entry_sluzby');
		$this->data['entry_doporuceny_balicek'] = $this->language->get('entry_doporuceny_balicek');
		$this->data['entry_cenny_balik'] = $this->language->get('entry_cenny_balik');
		$this->data['entry_ems'] = $this->language->get('entry_ems');
		$this->data['entry_obchodni_balik'] = $this->language->get('entry_obchodni_balik');

		$this->data['entry_tax'] = $this->language->get('entry_tax');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning']))  {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home',
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/shipping&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_shipping'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=shipping/royal_mail',
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=shipping/ceska_posta&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/shipping&token=' . $this->session->data['token'];

    // Doporučený balíček
		if (isset($this->request->post['doporuceny_balicek'])) {
			$this->data['doporuceny_balicek'] = $this->request->post['doporuceny_balicek'];
		} else {
			$this->data['doporuceny_balicek'] = $this->config->get('ceska_posta_doporuceny_balicek');
		}
		if (isset($this->request->post['doporuceny_balicek_ceny'])) {
			$this->data['doporuceny_balicek_ceny'] = $this->request->post['doporuceny_balicek_ceny'];
		} elseif ($this->config->get('ceska_posta_doporuceny_balicek')) {
			$this->data['doporuceny_balicek_ceny'] = $this->config->get('ceska_posta_doporuceny_balicek_ceny');
		} else {
		  	$this->data['doporuceny_balicek_ceny'] = '0.5:49,1:54,2:58';
    	}

    // Cenný balík
		if (isset($this->request->post['cenny_balik'])) {
			$this->data['cenny_balik'] = $this->request->post['cenny_balik'];
		} else {
			$this->data['cenny_balik'] = $this->config->get('ceska_posta_cenny_balik');
		}
		if (isset($this->request->post['cenny_balik_ceny'])) {
			$this->data['cenny_balik_ceny'] = $this->request->post['cenny_balik_ceny'];
		} elseif ($this->config->get('ceska_posta_cenny_balik_ceny')) {
			$this->data['cenny_balik_ceny'] = $this->config->get('ceska_posta_cenny_balik_ceny');
		} else {
		  	$this->data['cenny_balik_ceny'] = '2:58,5:65,10:77,15:91,20:107';
    	}

    // EMS
		if (isset($this->request->post['ems'])) {
			$this->data['ems'] = $this->request->post['ems'];
		} else {
			$this->data['ems'] = $this->config->get('ceska_posta_ems');
		}
		if (isset($this->request->post['ems_ceny'])) {
			$this->data['ems_ceny'] = $this->request->post['ems_ceny'];
		} elseif ($this->config->get('ceska_posta_ems_ceny')) {
			$this->data['ems_ceny'] = $this->config->get('ceska_posta_ems_ceny');
		} else {
		  $this->data['ems_ceny'] = '1:112,2:120,3:128,4:136,5:143,10:171,15:204,20:240';
    	}

    // Obchodní balík
		if (isset($this->request->post['obchodni_balik'])) {
			$this->data['obchodni_balik'] = $this->request->post['obchodni_balik'];
		} else {
			$this->data['obchodni_balik'] = $this->config->get('ceska_posta_obchodni_balik');
		}
		if (isset($this->request->post['obchodni_balik_ceny'])) {
			$this->data['obchodni_balik_ceny'] = $this->request->post['obchodni_balik_ceny'];
		} elseif ($this->config->get('ceska_posta_obchodni_balik_ceny')) {
			$this->data['obchodni_balik_ceny'] = $this->config->get('ceska_posta_obchodni_balik_ceny');
		} else {
			$this->data['obchodni_balik_ceny'] = '2:82,3:85,4:88,5:91,6:94,7:97,8:100,9:103,10:107,12:110,14:113,16:125,18:129,20:133,22:137,24:142,26:146,28:150,30:154';
    	}

		if (isset($this->request->post['ceska_posta_tax_class_id'])) {
			$this->data['ceska_posta_tax_class_id'] = $this->request->post['ceska_posta_tax_class_id'];
		} else {
			$this->data['ceska_posta_tax_class_id'] = $this->config->get('ceska_posta_tax_class_id');
		}

		if (isset($this->request->post['ceska_posta_geo_zone_id'])) {
			$this->data['ceska_posta_geo_zone_id'] = $this->request->post['ceska_posta_geo_zone_id'];
		} else {
			$this->data['ceska_posta_geo_zone_id'] = $this->config->get('ceska_posta_geo_zone_id');
		}

		if (isset($this->request->post['ceska_posta_status'])) {
			$this->data['ceska_posta_status'] = $this->request->post['ceska_posta_status'];
		} else {
			$this->data['ceska_posta_status'] = $this->config->get('ceska_posta_status');
		}

		if (isset($this->request->post['ceska_posta_sort_order'])) {
			$this->data['ceska_posta_sort_order'] = $this->request->post['ceska_posta_sort_order'];
		} else {
			$this->data['ceska_posta_sort_order'] = $this->config->get('ceska_posta_sort_order');
		}

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->template = 'shipping/ceska_posta.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/ceska_posta')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
