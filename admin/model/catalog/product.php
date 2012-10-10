<?php

use Nette\Utils\Arrays;
use Nette\Utils\Strings;



/**
 * @property \ModelCatalogCategory $model_catalog_category
 * @property \ModelCatalogAttribute $model_catalog_attribute
 * @property \ModelCatalogOption $model_catalog_option
 * @property \ModelCatalogManufacturer $model_catalog_manufacturer
 * @property \ModelLocalisationLanguage $model_localisation_language
 * @property \ModelLocalisationLengthClass $model_localisation_length_class
 * @property \ModelLocalisationWeightClass $model_localisation_weight_class
 */
class ModelCatalogProduct extends Model
{

	/**
	 * @param int $id
	 * @return \CatalogProduct
	 */
	public function find($id)
	{
		$product = new CatalogProduct($id);
		$product->load($this);

		return $product;
	}



	/**
	 * @param string $importId
	 * @param string $catalogNumber
	 * @return \CatalogProduct
	 */
	public function findByImport($importId, $catalogNumber)
	{
		$product = new CatalogProduct();
		$product->setImportId((string)$importId, (string)$catalogNumber);
		$product->load($this);

		return $product;
	}



	/**
	 * @param array $data
	 * @return int
	 */
	public function addProduct($data)
	{
		$this->database->beginTransaction();

		try {

			$product_id = $this->db->insert(DB_PREFIX . 'product', array(
				'model' => $data['model'],
				'sku' => $data['sku'],
				'import_id' => $data['import_id'],
				'upc' => $data['upc'],
				'location' => $data['location'],
				'quantity' => $data['quantity'],
				'minimum' => $data['minimum'],
				'subtract' => $data['subtract'],
				'stock_status_id' => $data['stock_status_id'],
				'date_available' => $data['date_available'],
				'manufacturer_id' => $data['manufacturer_id'],
				'shipping' => $data['shipping'],
				'price' => $data['price'],
				'points' => $data['points'],
				'weight' => $data['weight'],
				'weight_class_id' => $data['weight_class_id'],
				'length' => $data['length'],
				'width' => $data['width'],
				'height' => $data['height'],
				'length_class_id' => $data['length_class_id'],
				'status' => $data['status'],
				'tax_class_id' => $data['tax_class_id'],
				'sort_order' => $data['sort_order'],
				'date_added%sql' => 'NOW()',
			));

			if (isset($data['image'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
			}

			$this->addProductData($data, $product_id);

			$this->database->commit();
		} catch (\Exception $e) {
			$this->database->rollBack();
			throw $e;
		}

		$this->cache->delete('product');
		return $product_id;
	}


	public function editProduct($product_id, $data)
	{
		$this->database->beginTransaction();

		try {

			$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

			if (isset($data['image'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
			}

			$this->deleteProductData($product_id);
			$this->addProductData($data, $product_id);

			$this->database->commit();
		} catch (\Exception $e) {
			$this->database->rollBack();
			throw $e;
		}

		$this->cache->delete('product');
	}



	/**
	 * @param array $data
	 * @param $product_id
	 */
	protected function addProductData($data, $product_id)
	{
		$data = (array)$data + array_fill_keys(array(
			'product_attribute', 'product_description', 'product_discount', 'product_image',
			'product_option', 'product_related', 'product_reward', 'product_special',
			'product_tag', 'product_category', 'product_download', 'product_layout', 'product_store',
		), array());

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		foreach ($data['product_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
		}

		foreach ($data['product_attribute'] as $product_attribute) {
			if ($product_attribute['attribute_id']) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

				foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
				}
			}
		}

		foreach ($data['product_option'] as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_id = $this->db->insert(DB_PREFIX . 'product_option', array(
					'product_id' => $product_id,
					'option_id' => $product_option['option_id'],
					'required' => $product_option['required']
				) + (!empty($product_option['product_option_id']) ? array('product_option_id' => $product_option['product_option_id']) : array()));

				if (isset($product_option['product_option_value'])) {
					foreach ($product_option['product_option_value'] as $product_option_value) {
						$this->db->insert(DB_PREFIX . 'product_option_value', array(
							'product_option_id' => $product_option_id,
							'product_id' => $product_id,
							'option_id' => $product_option['option_id'],
							'option_value_id' => $product_option_value['option_value_id'],
							'quantity' => $product_option_value['quantity'],
							'subtract' => $product_option_value['subtract'],
							'price' => $product_option_value['price'],
							'price_prefix' => $product_option_value['price_prefix'],
							'points' => $product_option_value['points'],
							'points_prefix' => $product_option_value['points_prefix'],
							'weight' => $product_option_value['weight'],
							'weight_prefix' => $product_option_value['weight_prefix'],
						) + (!empty($product_option_value['product_option_value_id']) ? array('product_option_value_id' => $product_option_value['product_option_value_id']) : array()));
					}
				}
			} else {
				$this->db->insert(DB_PREFIX . 'product_option', array(
					'product_id' => $product_id,
					'option_id' => $product_option['option_id'],
					'option_value' => $product_option['option_value'],
					'required' => $product_option['required'],
				) + (!empty($product_option['product_option_id']) ? array('product_option_id' => $product_option['product_option_id']) : array()));
			}
		}

		foreach ($data['product_discount'] as $product_discount) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
		}

		foreach ($data['product_special'] as $product_special) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
		}

		foreach ($data['product_image'] as $product_image) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
		}

		foreach ($data['product_download'] as $download_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
		}

		foreach ($data['product_category'] as $category_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['product_related'] as $related_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_reward'] as $customer_group_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
		}

		foreach ($data['product_layout'] as $store_id => $layout) {
			if ($layout['layout_id']) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
			}
		}

		foreach ($data['product_tag'] as $language_id => $value) {
			if ($value) {
				$tags = explode(',', $value);

				foreach ($tags as $tag) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_tag SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', tag = '" . $this->db->escape(trim($tag)) . "'");
				}
			}
		}

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
	}



	public function copyProduct($product_id)
	{
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$data = $query->row;
			$data['keyword'] = '';
			$data['status'] = '0';
			$data = array_merge($data, $this->getProductData($product_id));

			$this->addProduct($data);
		}
	}


	/**
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function getProductData($product_id)
	{
		return array(
			'product_attribute' => $this->getProductAttributes($product_id),
			'product_description' => $this->getProductDescriptions($product_id),
			'product_discount' => $this->getProductDiscounts($product_id),
			'product_image' => $this->getProductImages($product_id),
			'product_option' => $this->getProductOptions($product_id),
			'product_related' => $this->getProductRelated($product_id),
			'product_reward' => $this->getProductRewards($product_id),
			'product_special' => $this->getProductSpecials($product_id),
			'product_tag' => $this->getProductTags($product_id),
			'product_category' => $this->getProductCategories($product_id),
			'product_download' => $this->getProductDownloads($product_id),
			'product_layout' => $this->getProductLayouts($product_id),
			'product_store' => $this->getProductStores($product_id),
		);
	}


	/**
	 * @param integer $product_id
	 */
	public function deleteProduct($product_id)
	{
		$this->database->beginTransaction();

		try {

			$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
			$this->deleteProductData($product_id);

			$this->database->commit();
		} catch (\Exception $e) {
			$this->database->rollBack();
			throw $e;
		}

		$this->cache->delete('product');
	}


	/**
	 * @param $product_id
	 */
	protected function deleteProductData($product_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
	}



	public function getProduct($product_id)
	{
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProducts($data = array())
	{
		if ($data) {
			$sql = "SELECT *, p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

			if (isset($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
			}

			$sql .= ' WHERE 1=1 ';
			if (!empty($data['filter_name'])) {
				$sql .= " AND LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
			}

			if (!empty($data['filter_model'])) {
				$sql .= " AND LCASE(p.model) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
			}

			if (!empty($data['filter_import_id'])) {
				$sql .= " AND p.import_id = '" . $this->db->escape($data['filter_import_id']) . "'";
			}

			if (!empty($data['filter_upc'])) {
				$sql .= " AND p.upc = '" . $this->db->escape($data['filter_upc']) . "'";
			}

			if (!empty($data['filter_price'])) {
				$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}

			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
			}

			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}

			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();

					$implode_data[] = "category_id = '" . (int)$data['filter_category_id'] . "'";

					$this->load->model('catalog/category');

					$categories = $this->model_catalog_category->getCategories($data['filter_category_id']);

					foreach ($categories as $category) {
						$implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
					}

					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}

			} elseif (isset($data['filter_category_id']) && $data['filter_category_id'] === "0") {
				$sql .= " AND p2c.category_id IS NULL ";
			}

			$sql .= " GROUP BY p.product_id";

			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY pd.name";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id'));

			if (!$product_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pd.name ASC");

				$product_data = $query->rows;

				$this->cache->set('product.' . (int)$this->config->get('config_language_id'), $product_data);
			}

			return $product_data;
		}
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id, $language_id = NULL) {
		$product_description_data = array();

		$langWhere = $language_id !== NULL ? " AND language_id = '" . (int)$language_id . "'" : NULL;
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "' $langWhere");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description']
			);
		}

		if ($language_id !== NULL) {
			return $product_description_data
				? $product_description_data[$language_id]
				: NULL;
		}

		return $product_description_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT pa.attribute_id, ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pa.attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'name'                          => $product_attribute['name'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();

				$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

				foreach ($product_option_value_query->rows as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}

				$product_option_data[] = array(
					'product_option_id'    => $product_option['product_option_id'],
					'option_id'            => $product_option['option_id'],
					'name'                 => $product_option['name'],
					'type'                 => $product_option['type'],
					'product_option_value' => $product_option_value_data,
					'required'             => $product_option['required']
				);
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);
			}
		}

		return $product_option_data;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}

	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getProductTags($product_id) {
		$product_tag_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$product_id . "'");

		$tag_data = array();

		foreach ($query->rows as $result) {
			$tag_data[$result['language_id']][] = $result['tag'];
		}

		foreach ($tag_data as $language => $tags) {
			$product_tag_data[$language] = implode(',', $tags);
		}

		return $product_tag_data;
	}

	public function getTotalProducts($data = array()) {
		$select = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p ";
		$joins = array(
			'pd' => "INNER JOIN " . DB_PREFIX . "product_description pd " .
				"ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')"
		);

		$where = array();
		if (!empty($data['filter_name'])) {
			$where[] = "LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$where[] = "LCASE(p.model) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$where[] = "p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$where[] = "p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$where[] = "p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_category_id'])) {
			$joins['p2c'] = "JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
		}

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$categoriesList = array("p2c.category_id = '" . (int)$data['filter_category_id'] . "'");

				$this->load->model('catalog/category');
				$categories = $this->model_catalog_category->getCategories($data['filter_category_id']);

				foreach ($categories as $category) {
					$categoriesList[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
				}

				$where[] = " (" . implode(' OR ', $categoriesList) . ")";

			} else {
				$where[] = " p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			$joins['p2c'] = 'INNER ' . $joins['p2c'];

		} elseif (isset($data['filter_category_id']) && $data['filter_category_id'] === "0") {
			$where[] = " p2c.category_id IS NULL ";
			$joins['p2c'] = 'LEFT ' . $joins['p2c'];
		}

		$where = $where ? 'WHERE ' . implode(' AND ', $where) : '';
		$query = $this->db->query($select . ' ' . implode(' ', $joins) . " $where");

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}


	public function disableAllFromManufacturer($manufacturer_id)
	{
		$this->database->table('product')
			->where('manufacturer_id', $manufacturer_id)
			->update(array('status' => 0));
	}



	/**
	 * @param int $product_id
	 * @param int $category_id
	 *
	 * @return bool
	 */
	public function addProductCategory($product_id, $category_id)
	{
		try {
			$this->database->table('product_to_category')->insert(array(
					'product_id' => $product_id,
					'category_id' => $category_id
				));

			return TRUE;

		} catch (\PDOException $e) {
			return FALSE;
		}
	}

}



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class CatalogProduct extends Nette\Object
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var array
	 */
	public $info = array(
		'model' => '',
		'sku' => '',
		'import_id' => '',
		'image' => '',
		'upc' => '',
		'location' => '',
		'quantity' => 0,
		'minimum' => 1,
		'subtract' => 0,
		'stock_status_id' => '',
		'date_available' => '',
		'manufacturer' => '',
		'shipping' => 1,
		'price' => 0.0,
		'points' => 0,
		'weight' => 0.0,
		'weight_class' => 'kg',
		'length' => 0.0,
		'width' => 0.0,
		'height' => 0.0,
		'length_class' => 'cm',
		'status' => 1,
		'keyword' => '',
		'tax_class_id' => '',
		'sort_order' => 0,
		'date_added%sql' => 'NOW()',
	);

	/**
	 * @var array
	 */
	public $description = array(
//		'cs' => array(
//			'name' => NULL,
//			'description' => NULL,
//			'meta_keyword' => NULL,
//			'meta_description' => NULL,
//		),
	);

	/**
	 * @var array
	 */
	public $tag = array();

	/**
	 * @var array
	 */
	public $store = array(
		'0' // default store
	);

	/**
	 * @var array
	 */
	public $layout = array();

	/**
	 * @var array
	 */
	public $attribute = array();

	/**
	 * @var array
	 */
	public $option = array();

	/**
	 * @var array
	 */
	public $discount = array();

	/**
	 * @var array
	 */
	public $related = array();

	/**
	 * @var array
	 */
	public $reward = array();

	/**
	 * @var array
	 */
	public $special = array();

	/**
	 * @var array
	 */
	public $category = array();

	/**
	 * @var array
	 */
	public $download = array();

	/**
	 * Get's converted to options
	 * @var array
	 */
	private $variants = array();

	/**
	 * @var array
	 */
	public $image = array();

	/**
	 * Get's converted to images;
	 * @var array
	 */
	private $remoteImages = array();



	/**
	 * @param int $id
	 */
	public function __construct($id = NULL)
	{
		$this->id = $id;
	}



	/**
	 * @param string $importId
	 * @param string $catalogNumber
	 */
	public function setImportId($importId, $catalogNumber)
	{
		$this->info['import_id'] = (string)$importId;
		$this->info['upc'] = (string)$catalogNumber;
	}



	/**
	 * @return array
	 */
	public function getImportId()
	{
		return array(
			$this->info['import_id'],
			$this->info['upc']
		);
	}



	/**
	 * @param string $name
	 * @param string $description
	 * @param array $other
	 * @param string $lang
	 */
	public function setDescription($name, $description = '', $other = array(), $lang = 'cs')
	{
		$old = isset($this->description[$lang]) ? $this->description[$lang] : array();
		$this->description[$lang] = array(
			'name' => (string)$name,
			'description' => (string)$description,
		) + $other + $old + array(
			'meta_keyword' => '',
			'meta_description' => '',
		);
	}



	/**
	 * @param array $info
	 */
	public function addInfo($info)
	{
		$this->info = array_map(function ($val) {
			return $val instanceof \XmlObject ? (string)$val : $val;
		}, (array)$info) + $this->info;
	}



	/**
	 * @param integer $option_id
	 * @param integer[] $variants
	 *
	 * @throws Nette\InvalidArgumentException
	 */
	public function addOption($option_id, array $variants)
	{
		static $valueDefaults = array(
			'product_option_value_id' => '',
			'quantity' => 0,
			'subtract' => 0,
			'price' => 0,
			'price_prefix' => '',
			'points' => 0,
			'points_prefix' => '',
			'weight' => 0,
			'weight_prefix' => '',
		);

		static $optionDefaults = array(
			'type' => 'select',
			'product_option_id' => 0,
			'required' => 1,
			'product_option_value' => array()
		);

		if (!array_filter($variants)) {
			throw new Nette\InvalidArgumentException("No variants provided.");
		}

		if (!isset($this->option[$option_id])) {
			$this->option[$option_id] = array('option_id' => $option_id) + $optionDefaults;
		}

		$values =& $this->option[$option_id]['product_option_value'];
		foreach ($variants as $option_value_id) {
			if ($this->hasOptionValue($option_id, $option_value_id)) {
				continue;
			}

			$values[] = array(
				'option_id' => $option_id,
				'option_value_id' => $option_value_id
			) + $valueDefaults;
		}
	}



	/**
	 * @param integer $option_id
	 * @param integer $option_value_id
	 * @return bool
	 */
	public function hasOptionValue($option_id, $option_value_id)
	{
		foreach ($this->option[$option_id]['product_option_value'] as $value) {
			if ($value['option_value_id'] == $option_value_id) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @param string $optionName
	 * @param array $productVariants
	 */
	public function addVariants($optionName, array $productVariants)
	{
		$this->variants[$optionName] = $productVariants;
	}



	/**
	 * @param \FeedImage $image
	 */
	public function addImage(\FeedImage $image)
	{
		$this->remoteImages[] = $image;
	}



	/**
	 * @param \ModelCatalogProduct $products
	 *
	 * @throws Nette\InvalidStateException
	 * @return \CatalogProduct
	 */
	public function load(ModelCatalogProduct $products)
	{
		if ($this->id) {
			if ($productInfo = $products->getProduct($this->id)) {
				$this->info = (array)$productInfo + $this->info;

			} else {
				throw new \Nette\InvalidStateException("Product data cannot be loaded.");
			}

		} else {
			$remoteId = array_filter(array_combine(
				array('filter_import_id', 'filter_upc'),
				$this->getImportId()
			));

			if ($remoteId) {
				if ($imported = $products->getProducts($remoteId)){
					$currentData = reset($imported);
					$this->id = $currentData['product_id'];
					$this->info = (array)$currentData + $this->info;
				}

			} else {
				throw new \Nette\InvalidStateException("Product data cannot be loaded.");
			}
		}

		if ($this->id) {
			$data = $products->getProductData($this->id);
			$this->attribute = $data['product_attribute'];
			$this->description = $data['product_description'];
			$this->discount = $data['product_discount'];
			foreach ($data['product_option'] as $option){
				$this->option[$option['option_id']] = $option;
			}
			$this->image = $data['product_image'];
			$this->related = $data['product_related'];
			$this->reward = $data['product_reward'];
			$this->special = $data['product_special'];
			$this->tag = $data['product_tag'];
			$this->category = $data['product_category'];
			$this->download = $data['product_download'];
			$this->layout = $data['product_layout'];
			$this->store = $data['product_store'];
		}

		return $this;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'product_id' => $this->id,
			'product_attribute' => $this->attribute,
			'product_description' => $this->description,
			'product_discount' => $this->discount,
			'product_image' => $this->image,
			'product_option' => $this->option,
			'product_related' => $this->related,
			'product_reward' => $this->reward,
			'product_special' => $this->special,
			'product_tag' => $this->tag,
			'product_category' => $this->category,
			'product_download' => $this->download,
			'product_layout' => $this->layout,
			'product_store' => $this->store,
		) + $this->info;
	}



	/**
	 */
	public function allowProduct()
	{
		$this->info['status'] = 1;
	}



	/**
	 * @param \ModelCatalogProduct $products
	 */
	public function save(ModelCatalogProduct $products)
	{
		// metrics & manufacturer
		$this->completeMapping(
			$products->model_localisation_length_class,
			$products->model_localisation_weight_class,
			$products->model_catalog_manufacturer,
			$products->model_localisation_language
		);

		// stock
		$this->info['stock_status_id'] = $products->config->get('config_stock_status_id');

		// pre-create reusable options
		$this->prepareOptions($products->model_catalog_option);

		// create local copies
		$this->importImages();

		// check if main image is set
		if (empty($this->info['image']) && ($first = reset($this->image))) {
			// set the first as main image
			$this->info['image'] = $first['image'];

			// remove from other pics
			unset($this->image[key($this->image)]);
		}

		if ($this->id) {
			$products->editProduct($this->id, $this->toArray());

		} else {
			$this->info['date_available'] = date('Y-m-d');
			$this->id = $products->addProduct($this->toArray());
		}
	}



	/**
	 * @param \ModelLocalisationLengthClass $lengthClass
	 * @param \ModelLocalisationWeightClass $weightClass
	 * @param \ModelCatalogManufacturer $manufacturer
	 * @param \ModelLocalisationLanguage $language
	 */
	private function completeMapping(
		\ModelLocalisationLengthClass $lengthClass,
		\ModelLocalisationWeightClass $weightClass,
		\ModelCatalogManufacturer $manufacturer,
		\ModelLocalisationLanguage $language)
	{
		if (isset($this->info['length_class'])) {
			$this->info['length_class_id'] = $lengthClass->getUnitId($this->info['length_class']);
		}
		if (isset($this->info['weight_class'])) {
			$this->info['weight_class_id'] = $weightClass->getUnitId($this->info['weight_class']);
		}
		if (isset($this->info['manufacturer'])) {
			$this->info['manufacturer_id'] = $manufacturer->getNameId($this->info['manufacturer']);
		}

		foreach ($this->description as $id => $desc) {
			if (is_numeric($id)) {
				continue;
			}

			unset($this->description[$id]);
			$this->description[$language->getCodeId($id)] = $desc;
		}
	}



	/**
	 * @param \ModelCatalogOption $options
	 */
	protected function prepareOptions(\ModelCatalogOption $options)
	{
		foreach ($this->variants as $optionName => $productVariants) {
			$option = $options->getByName($optionName);
			$option->addVariants($productVariants);
			$option->saveValues($options);

			$variantIds = array_map(function ($value) use ($option) {
				/** @var \CatalogOption $option */
				return $option->getVariantId($value);
			}, $productVariants);

			$this->addOption($option->id, $variantIds);
		}

		$this->variants = array();
	}



	/**
	 *
	 */
	protected function importImages()
	{
		$manufDir = isset($this->info['manufacturer'])
			? '/' . Strings::webalize($this->info['manufacturer'])
			: NULL;

		// images
		foreach ($this->remoteImages as $i => $image) {
			if (!$imageName = $image->download(DIR_IMAGE . 'data' . $manufDir, $this->info['manufacturer_id'], $this->info['upc'] ?: $this->info['model'])) {
				continue; // skip broken ones
			}

			$imageNameFile = 'data' . $manufDir . '/' . $imageName;
			if ($this->isImageRegistered($imageNameFile)) {
				continue;
			}

			/** @var \FeedImage $image */
			$this->image[] = array('image' => $imageNameFile, 'sort_order' => $i + 1);
		}

		// cleanup queue
		$this->remoteImages = array();
	}



	/**
	 * @param string $imageNameFile
	 *
	 * @return bool
	 */
	public function isImageRegistered($imageNameFile)
	{
		foreach ($this->image as $attached) {
			if ($attached['image'] === $imageNameFile) {
				return TRUE;
			}
		}

		if ($this->info['image'] === $imageNameFile) {
			return TRUE;
		}

		return FALSE;
	}

}
