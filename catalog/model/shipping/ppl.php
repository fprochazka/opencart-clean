<?php
class ModelShippingPPL extends Model
{

	function getQuote($address)
	{
		$this->load->language('shipping/ppl');

		if ($this->config->get('ppl_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ppl_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if (!$this->config->get('ppl_geo_zone_id')) {
				$status = TRUE;
			} elseif ($query->num_rows) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}

		} else {
			$status = FALSE;
		}

		if (!$status) {
			return array();
		}

		$weight = $this->cart->getWeight();
		$sub_total = $this->cart->getSubTotal();

		$quote_data = array();

		// Dobírka
		$cost2 = 0;
		if ($this->config->get('ppl_dobirka')) {
			$rates = explode(',', $this->config->get('ppl_dobirka_ceny'));
			foreach ($rates as $rate) {
				$data = explode(':', $rate);
				if ($data[0] >= $sub_total) {
					if (isset($data[1])) {
						$cost2 = $data[1];
					}
					break;
				}
			}
		}

		// PPL balík CZ
		if ($this->config->get('ppl_ppl_cz')) {
			$cost = 0;
			$rates = explode(',', $this->config->get('ppl_ppl_cz_ceny'));
			foreach ($rates as $rate) {
				$data = explode(':', $rate);
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost = $data[1];
					}
					break;
				}
			}

			if ($cost) {
				$quote_data['ppl_cz'] = array(
					'id' => 'ppl.ppl_cz',
					'code' => 'ppl.ppl_cz',
					'title' => $this->language->get('text_ppl_cz'),
					'cost' => $cost,
					'tax_class_id' => $this->config->get('ppl_tax_class_id'),
					'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
				);

				if ($cost2) {
					$quote_data['ppl_cz_dobirka'] = array(
						'id' => 'ppl.ppl_cz_dobirka',
						'code' => 'ppl.ppl_cz_dobirka',
						'title' => $this->language->get('text_ppl_cz') . ' + ' . $this->language->get('text_dobirka'),
						'cost' => $cost + $cost2,
						'tax_class_id' => $this->config->get('ppl_tax_class_id'),
						'text' => $this->currency->format($this->tax->calculate($cost + $cost2, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
					);
				}
			}
		}

		// PPL export SK
		if ($this->config->get('ppl_ppl_sk')) {
			$cost = 0;
			$rates = explode(',', $this->config->get('ppl_ppl_sk_ceny'));
			foreach ($rates as $rate) {
				$data = explode(':', $rate);
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost = $data[1];
					}
					break;
				}
			}

			if ($cost) {
				$quote_data['ppl_sk'] = array(
					'id' => 'ppl.ppl_sk',
					'code' => 'ppl.ppl_sk',
					'title' => $this->language->get('text_ppl_sk'),
					'cost' => $cost,
					'tax_class_id' => $this->config->get('ppl_tax_class_id'),
					'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
				);
				if ($cost2) {
					$quote_data['ppl_sk_dobirka'] = array(
						'id' => 'ppl.ppl_sk_dobirka',
						'code' => 'ppl.ppl_sk_dobirka',
						'title' => $this->language->get('text_ppl_sk') . ' + ' . $this->language->get('text_dobirka'),
						'cost' => $cost + $cost2,
						'tax_class_id' => $this->config->get('ppl_tax_class_id'),
						'text' => $this->currency->format($this->tax->calculate($cost + $cost2, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
					);
				}
			}
		}

		// PPL export DE
		if ($this->config->get('ppl_ppl_de')) {
			$cost = 0;
			$rates = explode(',', $this->config->get('ppl_ppl_de_ceny'));
			foreach ($rates as $rate) {
				$data = explode(':', $rate);
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost = $data[1];
					}
					break;
				}
			}

			if ($cost) {
				$quote_data['ppl_de'] = array(
					'id' => 'ppl.ppl_de',
					'code' => 'ppl.ppl_de',
					'title' => $this->language->get('text_ppl_de'),
					'cost' => $cost,
					'tax_class_id' => $this->config->get('ppl_tax_class_id'),
					'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
				);
				if ($cost2) {
					$quote_data['ppl_de_dobirka'] = array(
						'id' => 'ppl.ppl_de_dobirka',
						'code' => 'ppl.ppl_de_dobirka',
						'title' => $this->language->get('text_ppl_de') . ' + ' . $this->language->get('text_dobirka'),
						'cost' => $cost + $cost2,
						'tax_class_id' => $this->config->get('ppl_tax_class_id'),
						'text' => $this->currency->format($this->tax->calculate($cost + $cost2, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
					);
				}
			}
		}

		// PPL export PL
		if ($this->config->get('ppl_ppl_pl')) {
			$cost = 0;
			$rates = explode(',', $this->config->get('ppl_ppl_pl_ceny'));
			foreach ($rates as $rate) {
				$data = explode(':', $rate);
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost = $data[1];
					}
					break;
				}
			}

			if ($cost) {
				$quote_data['ppl_pl'] = array(
					'id' => 'ppl.ppl_pl',
					'code' => 'ppl.ppl_pl',
					'title' => $this->language->get('text_ppl_pl'),
					'cost' => $cost,
					'tax_class_id' => $this->config->get('ppl_tax_class_id'),
					'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
				);
				if ($cost2) {
					$quote_data['ppl_pl_dobirka'] = array(
						'id' => 'ppl.ppl_pl_dobirka',
						'code' => 'ppl.ppl_pl_dobirka',
						'title' => $this->language->get('text_ppl_pl') . ' + ' . $this->language->get('text_dobirka'),
						'cost' => $cost + $cost2,
						'tax_class_id' => $this->config->get('ppl_tax_class_id'),
						'text' => $this->currency->format($this->tax->calculate($cost + $cost2, $this->config->get('ppl_tax_class_id'), $this->config->get('config_tax')))
					);
				}
			}
		}

		if (!$quote_data) {
			return array();
		}

		return array(
			'id' => 'ppl',
			'code' => 'ppl',
			'title' => $this->language->get('text_title'),
			'quote' => $quote_data,
			'sort_order' => $this->config->get('ppl_sort_order'),
			'error' => FALSE
		);
	}
}
