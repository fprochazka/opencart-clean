<?php
class ModelCatalogCategory extends Model
{

	public function getCategory($category_id)
	{
		$cacheKey = 'catalog.category.info.' . (int)$category_id . '.' . (int)$this->config->get('config_language_id');

		if (!$category_data = $this->cache->get($cacheKey)) {
			$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");
			$category_data = $query->row ? (array)$query->row : $query->row;

			$this->cache->set($cacheKey, $category_data);
		}

		return $category_data;
	}

	public function getCategories($parent_id = 0)
	{
		$cacheKey = 'catalog.category.children.' . (int)$parent_id . '.' . (int)$this->config->get('config_language_id');
		if (NULL === ($category_data = $this->cache->get($cacheKey))) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");
			$category_data = (array)$query->rows;

			$this->cache->set($cacheKey, $category_data);
		}

		return $category_data;
	}

	public function getCategoriesByParentId($category_id) {
		$category_data = array();

		$category_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$category_id . "'");

		foreach ($category_query->rows as $category) {
			$category_data[] = $category['category_id'];

			$children = $this->getCategoriesByParentId($category['category_id']);

			if ($children) {
				$category_data = array_merge($children, $category_data);
			}
		}

		return $category_data;
	}

	public function getCategoryLayoutId($category_id)
	{
		$cacheKey = 'catalog.category.layout.'. (int)$category_id;
		if (NULL === ($category_data = $this->cache->get($cacheKey))) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
			$category_data = $query->num_rows ? $query->row['layout_id'] : $this->config->get('config_layout_category');
			$this->cache->set($cacheKey, $category_data ?: 0);
		}

		return $category_data;
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row['total'];
	}
}
