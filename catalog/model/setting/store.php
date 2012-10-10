<?php
class ModelSettingStore extends Model
{

	public function getStores($data = array())
	{
		$cacheKey = 'setting.store.ordered';
		if (!$store_data = $this->cache->get($cacheKey)) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY url");
			$store_data = $query->rows;

			$this->cache->set($cacheKey, $store_data);
		}

		return $store_data;
	}
}
