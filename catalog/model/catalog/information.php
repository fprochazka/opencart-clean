<?php
class ModelCatalogInformation extends Model
{

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getInformations()
	{
		$cacheKey = 'catalog.information.front.' . (int)$this->config->get('config_language_id');
		if (NULL === ($information_data = $this->cache->get($cacheKey))) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");
			$information_data = $query->rows;

			$this->cache->set($cacheKey, $information_data);
		}

		return $information_data;
	}

	public function getInformationLayoutId($information_id)
	{
		$cacheKey = 'catalog.information.layout.' . (int)$information_id;
		if (NULL === ($information_data = $this->cache->get($cacheKey))) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
			$information_data = $query->num_rows
				? $query->row['layout_id']
				: $this->config->get('config_layout_information');

			$this->cache->set($cacheKey, $information_data ?: 0);
		}

		return $information_data;
	}
}
