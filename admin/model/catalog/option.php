<?php

use Nette\Utils\Strings;


/**
 * @property \ModelLocalisationLanguage $model_localisation_language
 */
class ModelCatalogOption extends Model
{

	/**
	 * @var array
	 */
	private $identityMapByName = array();



	public function addOption($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$option_id = $this->db->getLastId();

		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");

				$option_value_id = $this->db->getLastId();

				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
				}
			}
		}

		return $option_id;
	}



	/**
	 * @param int $option_id
	 * @param array $data
	 */
	public function editOption($option_id, $data)
	{
		$this->db->query("UPDATE `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE option_id = '" . (int)$option_id . "'");

		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "' AND language_id = '" . (int)$language_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				$this->editOptionValue($option_id, $option_value);
			}
		}
	}



	/**
	 * @param string $option_id
	 * @param array $option_value
	 * @return int
	 */
	public function editOptionValue($option_id, $option_value)
	{
		$insert = array(
			'option_id' => $option_id,
			'image' => html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'),
			'sort_order' => $option_value['sort_order'],
		);

		if ($option_value_id = $option_value['option_value_id']) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "option_value " .
				"WHERE option_id = '" . (int)$option_id . "' AND option_value_id = '" . (int)$option_value_id . "'");

			$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description " .
				"WHERE option_id = '" . (int)$option_id . "' AND option_value_id = '" . (int)$option_value_id . "'");

			$this->db->insert(DB_PREFIX . 'option_value', $insert + array('option_value_id' => $option_value_id));

		} else {
			$option_value_id = $this->db->insert(DB_PREFIX . 'option_value', $insert);
		}

		foreach ($option_value['option_value_description'] as $language_id => $valueDesc) {
			$this->db->insert(DB_PREFIX . 'option_value_description', array(
				'option_value_id' => $option_value_id,
				'language_id' => $language_id,
				'option_id' => $option_id,
				'name' => $valueDesc['name']
			));
		}

		return $option_value_id;
	}


	public function deleteOption($option_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");
	}

	public function getOption($option_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id = '" . (int)$option_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getOptions($data = array(), $language = NULL) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)($language ?: $this->config->get('config_language_id')) . "'";

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND LCASE(od.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		$sort_data = array(
			'od.name',
			'o.type',
			'o.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY od.name";
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
	}

	public function getOptionDescriptions($option_id) {
		$option_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");

		foreach ($query->rows as $result) {
			$option_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $option_data;
	}

	public function getOptionValues($option_id) {
		$option_value_data = array();

		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order ASC");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}

		return $option_value_data;
	}

	public function getOptionValueDescriptions($option_id) {
		$option_value_data = array();

		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();

			$option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value['option_value_id'] . "'");

			foreach ($option_value_description_query->rows as $option_value_description) {
				$option_value_description_data[$option_value_description['language_id']] = array('name' => $option_value_description['name']);
			}

			$option_value_data[] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value_description_data,
				'image'                    => $option_value['image'],
				'sort_order'               => $option_value['sort_order']
			);
		}

		return $option_value_data;
	}

	public function getTotalOptions() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "option`");

		return $query->row['total'];
	}



	/**
	 * @param string $name
	 * @return \CatalogOption
	 */
	public function getByName($name)
	{
		if (isset($this->identityMapByName[$name])) {
			return $this->identityMapByName[$name];
		}

		// just for the lolz
		$this->load->model('localisation/language');

		$options = $this->getOptions(array(
			'filter_name' => $name,
		), ModelLocalisationLanguage::ENGLISH); // 1 is english

		if (!$options) {
			$option = new CatalogOption($name);
			$option->addDescription($name);
			$option->save($this);

		} else {
			$data = reset($options);
			$option = new CatalogOption(NULL, $data['option_id']);
			$option->info = (array)$data + $option->info;
			$option->load($this);
		}

		return $this->manage($option);
	}



	/**
	 * @param CatalogOption $option
	 * @return \CatalogOption
	 */
	public function manage(CatalogOption $option)
	{
		foreach ($option->description as $desc) {
			$this->identityMapByName[$desc['name']] = $option;
		}

		return $option;
	}

}



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class CatalogOption extends Nette\Object
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var array
	 */
	public $info = array(
		'type' => 'select',
		'sort_order' => 1,
	);

	/**
	 * @var array
	 */
	public $description = array();

	/**
	 * @var array
	 */
	public $value = array();

	/**
	 * @var array
	 */
	private $variants = array();



	/**
	 * @param string $name
	 * @param int $id
	 */
	public function __construct($name = NULL, $id = NULL)
	{
		$this->id = $id;
		$this->addDescription($name, \ModelLocalisationLanguage::ENGLISH);
	}



	/**
	 * @param string $name
	 * @param string $lang
	 */
	public function addDescription($name, $lang = 'cs')
	{
		$this->description[$lang] = array(
			'name' => $name
		);
	}



	/**
	 * @param string $variant
	 * @param string $lang
	 *
	 * @return
	 */
	public function addVariant($variant, $lang = 'cs')
	{
		foreach ($this->value as $valueDesc) {
			foreach ($valueDesc['option_value_description'] as $desc) {
				if (Strings::webalize($variant) === Strings::webalize($desc['name'])) {
					return;
				}
			}
		}

		foreach ($this->variants as $registered) {
			if (Strings::webalize($variant) === Strings::webalize($registered['name'])) {
				return;
			}
		}

		$this->variants[] = array(
			'lang' => $lang,
			'name' => $variant
		);
	}



	/**
	 * @param array $variants
	 * @param string $lang
	 */
	public function addVariants(array $variants, $lang = 'cs')
	{
		foreach ($variants as $variant) {
			$this->addVariant($variant, $lang);
		}
	}



	/**
	 * @param string $variant
	 *
	 * @throws Nette\InvalidStateException
	 * @throws Nette\ArgumentOutOfRangeException
	 * @return integer
	 */
	public function getVariantId($variant)
	{
		foreach ($this->value as $valueDesc) {
			foreach ($valueDesc['option_value_description'] as $desc) {
				if (Strings::webalize($variant) === Strings::webalize($desc['name'])) {
					if ($id = $valueDesc['option_value_id']) {
						return $id;
					}

					throw new Nette\InvalidStateException("You should persist the option, before asking for id's.");
				}
			}
		}

		throw new Nette\ArgumentOutOfRangeException("Missing variant $variant.");
	}



	/**
	 * @param \ModelCatalogOption $options
	 */
	public function load(ModelCatalogOption $options)
	{
		$this->description = $options->getOptionDescriptions($this->id);
		$this->value = $options->getOptionValueDescriptions($this->id);
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'option_id' => $this->id,
			'option_description' => $this->description,
			'option_value' => $this->value,
		) + $this->info;
	}



	/**
	 * @param \ModelCatalogOption $options
	 */
	public function save(ModelCatalogOption $options)
	{
		$this->completeMapping($options->model_localisation_language);
		$this->prepareVariants($options->model_localisation_language);

		if ($this->id) {
			$options->editOption($this->id, $this->toArray());

		} else {
			$this->id = $options->addOption($this->toArray());
		}
	}



	/**
	 * @param \ModelCatalogOption $options
	 * @return mixed
	 */
	public function saveValues(ModelCatalogOption $options)
	{
		if (!$this->id) {
			$this->save($options);
		}

		if (!$this->variants) {
			return; // there is basically nothing to do (hope se)
		}

		$this->prepareVariants($options->model_localisation_language);
		foreach ($this->value as &$value) {
			$value['option_value_id'] = $options->editOptionValue($this->id, $value);
		}
	}



	/**
	 * @param \ModelLocalisationLanguage $language
	 */
	private function prepareVariants(ModelLocalisationLanguage $language)
	{
		foreach ($this->variants as $variant) {
			$lang = is_numeric($variant['lang'])
				? $variant['lang']
				: $language->getCodeId($variant['lang']);

			$this->value[] = array(
				'image' => 'no_image.jpg',
				'sort_order' => 1,
				'option_value_id' => NULL,
				'option_value_description' => array(
					$lang => array(
						'name' => $variant['name']
					)
				)
			);
		}

		$this->variants = array();
	}



	/**
	 * @param \ModelLocalisationLanguage $language
	 */
	private function completeMapping(ModelLocalisationLanguage $language)
	{
		foreach ($this->description as $id => $desc) {
			if (is_numeric($id)) {
				continue;
			}

			unset($this->description[$id]);
			$this->description[$language->getCodeId($id)] = $desc;
		}
	}

}
