<?php
class ModelDesignLayout extends Model
{

	public function getLayout($route)
	{
		$cacheKey = 'design.layout.route.' . urlencode($route);
		if (NULL === ($layout_data = $this->cache->get($cacheKey))){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE '" . $this->db->escape($route) . "' LIKE CONCAT(route, '%') AND store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY route ASC LIMIT 1");
			$layout_data = $query->num_rows ? $query->row['layout_id'] : 0;

			$this->cache->set($cacheKey, $layout_data);
		}

		return $layout_data;
	}

}
