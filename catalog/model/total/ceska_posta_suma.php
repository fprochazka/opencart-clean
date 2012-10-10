<?php
class ModelTotalCeskaPostaSuma extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method']) && isset($this->session->data['payment_method']) && $this->config->get('ceska_posta_suma_status')) {
			
			$this->load->language('total/ceska_posta_suma');
			$this->load->model('localisation/currency');
		
			$cost = 0;
			if (isset($this->session->data['payment_method']['cost'])) {
				$cost = $this->session->data['shipping_method']['cost'] + $this->session->data['payment_method']['cost'];
			} else {
				$cost = $this->session->data['shipping_method']['cost'];
			}

			$total_data[] = array( 
        	'title'      => $this->language->get('heading_title') . ':',
        	'text'       => $this->currency->format($cost),
        	'value'      => $cost,
			'sort_order' => $this->config->get('ceska_posta_suma_sort_order')
			);
			
			if (isset($this->session->data['shipping_method']['tax_class_id'])) {
				if (!isset($taxes[$this->session->data['shipping_method']['tax_class_id']])) {
					$taxes[$this->session->data['shipping_method']['tax_class_id']] = $this->session->data['shipping_method']['cost'] / 100 * $this->tax->getRate($this->session->data['shipping_method']['tax_class_id']);
				} else {
					$taxes[$this->session->data['shipping_method']['tax_class_id']] += $this->session->data['shipping_method']['cost'] / 100 * $this->tax->getRate($this->session->data['shipping_method']['tax_class_id']);
				}
			}
			
			if (isset($this->session->data['payment_method']['tax_class_id'])) {
				if (!isset($taxes[$this->session->data['payment_method']['tax_class_id']])) {
					$taxes[$this->session->data['payment_method']['tax_class_id']] = $this->session->data['payment_method']['cost'] / 100 * $this->tax->getRate($this->session->data['payment_method']['tax_class_id']);
				} else {
					$taxes[$this->session->data['payment_method']['tax_class_id']] += $this->session->data['payment_method']['cost'] / 100 * $this->tax->getRate($this->session->data['payment_method']['tax_class_id']);
				}
			}
			
			$total += $cost;
		}			
	}
}
?>