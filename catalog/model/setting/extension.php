<?php
class ModelSettingExtension extends Model
{

	public function getExtensions($type)
	{
		$cacheKey = 'setting.extension.type.' . urlencode($type);
		if (!$extension_data = $this->cache->get($cacheKey)){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'");
			$extension_data = $query->rows;

			$this->cache->set($cacheKey, $extension_data);
		}

		return $extension_data;
	}

}
