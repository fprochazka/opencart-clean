<?php
class ModelPaymentCeskaPostaDobirka extends Model
{

	public function getMethod($address)
	{
		$this->load->language('payment/ceska_posta_dobirka');

		if ($this->config->get('ceska_posta_dobirka_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ceska_posta_dobirka_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if (!$this->config->get('ceska_posta_dobirka_geo_zone_id')) {
				$status = TRUE;
			} elseif ($query->num_rows) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
		} else {
			$status = FALSE;
		}

		$method_data = array();

		if ($status) {
			$sub_total = $this->cart->getSubTotal();
			$cost = 0;
			if ($this->config->get('ceska_posta_dobirka_ceny')) {
				$rates = explode(',', $this->config->get('ceska_posta_dobirka_ceny'));
				foreach ($rates as $rate) {
					$data = explode(':', $rate);
					if ($data[0] >= $sub_total) {
						if (isset($data[1])) {
							$cost = $data[1];
						}
						break;
					}
				}
			}

			$method_data = array(
				'id' => 'ceska_posta_dobirka',
				'code' => 'ceska_posta_dobirka',
				'title' => $this->language->get('text_title'),
				'sort_order' => $this->config->get('ceska_posta_dobirka_sort_order'),
				'tax_class_id' => $this->config->get('ceska_posta_dobirka_tax_class_id'),
				'cost' => $cost,
				'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('ceska_posta_dobirka_tax_class_id'), $this->config->get('config_tax')))
			);
		}

		return $method_data;
	}
}
