<?php


/**
 * @property \Loader $load
 * @property \Registry $registry
 * @property \Nette\DI\Container|\SystemContainer $nette
 * @property \Nette\Database\Connection $database
 * @property \Language $language
 * @property \DB $db
 * @property \Url $url
 * @property \Document $document
 * @property \Request $request
 * @property \Response $response
 * @property \Config $config
 * @property \Session $session
 * @property \Cache $cache
 * @property \Currency $currency
 *
 * @property \ModelCatalogCategory $model_catalog_category
 * @property \ModelCatalogProduct $model_catalog_product
 * @property \ModelCatalogInformation $model_catalog_information
 * @property \ModelDesignLayout $model_design_layout
 * @property \ModelSettingExtension $model_setting_extension
 */
abstract class Controller
{

	protected $registry;
	protected $id;
	protected $layout;
	protected $template;
	protected $children = array();
	protected $data = array();
	protected $output;

	public function __construct($registry)
	{
		$this->registry = $registry;
	}


	/**
	 * @param string $key
	 * @return Model|NULL
	 */
	public function __get($key)
	{
		/** @var \Loader $loader */
		$loader = $this->registry->get('load');
		return $loader->__get($key);
	}

	public function __set($key, $value)
	{
		$this->registry->set($key, $value);
	}

	/**
	 * @param string $target
	 * @param array $args
	 *
	 * @return string
	 */
	protected function link($target, $args = array())
	{
		$args = is_array($args) ? '&' . http_build_query($args) : NULL;
		return $this->url->link($target, 'token=' . $this->session->data['token'] . $args, 'SSL');
	}

	protected function forward($route, $args = array())
	{
		return new Action($route, $args);
	}

	protected function redirect($url, $status = 302)
	{
		@header('Status: ' . $status);
		@header('Location: ' . str_replace('&amp;', '&', $url));
		exit();
	}

	protected function getChild($child, $args = array())
	{
		$action = new Action($child, $args);
		$file = $action->getFile();
		$class = $action->getClass();
		$method = $action->getMethod();

		if (file_exists($file)) {
			require_once($file);

			$controller = new $class($this->registry);
			$controller->$method($args);

			return $controller->output;
		} else {
			trigger_error('Error: Could not load controller ' . $child . '!');
			exit();
		}
	}



	protected function render()
	{
		foreach ($this->children as $child) {
			$this->data[basename($child)] = $this->getChild($child);
		}

		if (file_exists(DIR_TEMPLATE . $this->template)) {
			$this->output = $this->renderTemplate(DIR_TEMPLATE . $this->template, $this->data);
			return $this->output;

    	} else {
			trigger_error('Error: Could not load template ' . DIR_TEMPLATE . $this->template . '!');
			exit();
    	}
	}



	/**
	 * @param string $file
	 * @param array $params
	 * @return string
	 */
	private static function renderTemplate($file, $params)
	{
		ob_start();

		extract($params);
		require $file;

		return ob_get_clean();
	}

}
