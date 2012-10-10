<?php
class Weight {
	private $weights = array();

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
		$this->cache = $registry->get('cache');

		$cacheKey = 'weight_class.front.' . (int)$this->config->get('config_language_id');
		if (NULL === ($this->weights = $this->cache->get($cacheKey))){
			$weight_class_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

			$this->weights = array();
			foreach ($weight_class_query->rows as $result) {
				$this->weights[$result['weight_class_id']] = array(
					'weight_class_id' => $result['weight_class_id'],
					'title'           => $result['title'],
					'unit'            => $result['unit'],
					'value'           => $result['value']
				);
			}

			$this->cache->set($cacheKey, $this->weights);
		}
  	}

  	public function convert($value, $from, $to) {
		if ($from == $to) {
      		return $value;
		}

		if (!isset($this->weights[$from]) || !isset($this->weights[$to])) {
			return $value;
		} else {
			$from = $this->weights[$from]['value'];
			$to = $this->weights[$to]['value'];

			return $value * ($to / $from);
		}
  	}

	public function format($value, $weight_class_id, $decimal_point = '.', $thousand_point = ',') {
		if (isset($this->weights[$weight_class_id])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->weights[$weight_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	public function getUnit($weight_class_id) {
		if (isset($this->weights[$weight_class_id])) {
    		return $this->weights[$weight_class_id]['unit'];
		} else {
			return '';
		}
	}
}
