<?php
class ControllerShippingPPL extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shipping/ppl');

		$this->document->title = $this->language->get('heading_title');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('ppl', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?token=' . $this->session->data['token'] . '&route=extension/shipping');
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_sluzby'] = $this->language->get('entry_sluzby');
		$this->data['entry_ppl_cz'] = $this->language->get('entry_ppl_cz');
		$this->data['entry_ppl_sk'] = $this->language->get('entry_ppl_sk');
		$this->data['entry_ppl_de'] = $this->language->get('entry_ppl_de');
		$this->data['entry_ppl_pl'] = $this->language->get('entry_ppl_pl');
		$this->data['entry_dobirka'] = $this->language->get('entry_dobirka');

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
       		'href'      => HTTPS_SERVER . 'index.php?token=' . $this->session->data['token'] . '&route=extension/shipping',
       		'text'      => $this->language->get('text_shipping'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=shipping/ppl',
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?token=' . $this->session->data['token'] . '&route=shipping/ppl';

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?token=' . $this->session->data['token'] . '&route=extension/shipping';

    // PPL balík CZ
		if (isset($this->request->post['ppl_cz'])) {
			$this->data['ppl_cz'] = $this->request->post['ppl_cz'];
		} else {
			$this->data['ppl_cz'] = $this->config->get('ppl_ppl_cz');
		}
		if (isset($this->request->post['ppl_cz_ceny'])) {
			$this->data['ppl_cz_ceny'] = $this->request->post['ppl_cz_ceny'];
		} elseif ($this->config->get('ppl_ppl_cz_ceny')) {
			$this->data['ppl_cz_ceny'] = $this->config->get('ppl_ppl_cz_ceny');
		} else {
		  $this->data['ppl_cz_ceny'] = '1:113,3:122,5:128,7:138,10:155,12:168,15:179,20:192,25:204,30:216,35:284,40:371,50:482';
    }

    // PPL Export Slovensko
		if (isset($this->request->post['ppl_sk'])) {
			$this->data['ppl_sk'] = $this->request->post['ppl_sk'];
		} else {
			$this->data['ppl_sk'] = $this->config->get('ppl_ppl_sk');
		}
		if (isset($this->request->post['ppl_sk_ceny'])) {
			$this->data['ppl_sk_ceny'] = $this->request->post['ppl_sk_ceny'];
		} elseif ($this->config->get('ppl_ppl_sk_ceny')) {
			$this->data['ppl_sk_ceny'] = $this->config->get('ppl_ppl_sk_ceny');
		} else {
		  $this->data['ppl_sk_ceny'] = '1:240,3:290,5:320,7:360,10:400,12:430,15:460,20:520,25:580,30:650,35:770,40:910,50:1080';
    }

    // PPL Export Německo
		if (isset($this->request->post['ppl_de'])) {
			$this->data['ppl_de'] = $this->request->post['ppl_de'];
		} else {
			$this->data['ppl_de'] = $this->config->get('ppl_ppl_de');
		}
		if (isset($this->request->post['ppl_de_ceny'])) {
			$this->data['ppl_de_ceny'] = $this->request->post['ppl_de_ceny'];
		} elseif ($this->config->get('ppl_ppl_de_ceny')) {
			$this->data['ppl_de_ceny'] = $this->config->get('ppl_ppl_de_ceny');
		} else {
		  $this->data['ppl_de_ceny'] = '1:449,3:467,5:685,7:687,10:707,12:714,15:758,20:828,25:863,30:929';
    }

    // PPL Export Polsko
		if (isset($this->request->post['ppl_pl'])) {
			$this->data['ppl_pl'] = $this->request->post['ppl_pl'];
		} else {
			$this->data['ppl_pl'] = $this->config->get('ppl_ppl_pl');
		}
		if (isset($this->request->post['ppl_pl_ceny'])) {
			$this->data['ppl_pl_ceny'] = $this->request->post['ppl_pl_ceny'];
		} elseif ($this->config->get('ppl_ppl_pl_ceny')) {
			$this->data['ppl_pl_ceny'] = $this->config->get('ppl_ppl_pl_ceny');
		} else {
			$this->data['ppl_pl_ceny'] = '1:495,3:660,5:743,7:784,10:887,12:969,15:1031,20:1093,25:1134,30:1238';
    }

    // Dobírka
		if (isset($this->request->post['dobirka'])) {
			$this->data['dobirka'] = $this->request->post['dobirka'];
		} else {
			$this->data['dobirka'] = $this->config->get('ppl_dobirka');
		}
		if (isset($this->request->post['dobirka_ceny'])) {
			$this->data['dobirka_ceny'] = $this->request->post['dobirka_ceny'];
		} elseif ($this->config->get('ppl_dobirka_ceny')) {
			$this->data['dobirka_ceny'] = $this->config->get('ppl_dobirka_ceny');
		} else {
			$this->data['dobirka_ceny'] = '1000:50,5000:70,20000:80,50000:150,80000:250';
    }

		if (isset($this->request->post['ppl_tax_class_id'])) {
			$this->data['ppl_tax_class_id'] = $this->request->post['ppl_tax_class_id'];
		} else {
			$this->data['ppl_tax_class_id'] = $this->config->get('ppl_tax_class_id');
		}

		if (isset($this->request->post['ppl_geo_zone_id'])) {
			$this->data['ppl_geo_zone_id'] = $this->request->post['ppl_geo_zone_id'];
		} else {
			$this->data['ppl_geo_zone_id'] = $this->config->get('ppl_geo_zone_id');
		}

		if (isset($this->request->post['ppl_status'])) {
			$this->data['ppl_status'] = $this->request->post['ppl_status'];
		} else {
			$this->data['ppl_status'] = $this->config->get('ppl_status');
		}

		if (isset($this->request->post['ppl_sort_order'])) {
			$this->data['ppl_sort_order'] = $this->request->post['ppl_sort_order'];
		} else {
			$this->data['ppl_sort_order'] = $this->config->get('ppl_sort_order');
		}

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->template = 'shipping/ppl.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/ppl')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
