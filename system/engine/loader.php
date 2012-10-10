<?php


final class Loader
{
	/**
	 * @var \Registry
	 */
	protected $registry;



	/**
	 * @param \Registry $registry
	 */
	public function __construct($registry)
	{
		$this->registry = $registry;
	}



	/**
	 * @param string $key
	 * @return \Model|NULL
	 */
	public function __get($key)
	{
		if ($this->registry->has($key)) {
			return $this->registry->get($key);
		}

		if (substr($key, 0, 6) === 'model_') {
			return $this->model(implode('/', explode('_', substr($key, 6), 2)));
		}

		return NULL;
	}


	public function __set($key, $value)
	{
		$this->registry->set($key, $value);
	}

	public function library($library)
	{
		$file = DIR_SYSTEM . 'library/' . $library . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
			trigger_error('Error: Could not load library ' . $library . '!');
			exit();
		}
	}


	/**
	 * @param string $model
	 * @return object
	 */
	public function model($model)
	{
		if ($this->registry->has($name = 'model_' . str_replace('/', '_', $model))) {
			return $this->registry->get($name);
		}

		$file  = DIR_APPLICATION . 'model/' . $model . '.php';
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

		if (file_exists($file)) {
			include_once($file);

			$instance = new $class($this->registry);
			$this->registry->set($name, $instance);
			return $instance;

		} else {
			trigger_error('Error: Could not load model ' . $model . '!');
			exit();
		}
	}

	public function database($driver, $hostname, $username, $password, $database, $prefix = NULL, $charset = 'UTF8')
	{
		$file  = DIR_SYSTEM . 'database/' . $driver . '.php';
		$class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);

		if (file_exists($file)) {
			include_once($file);

			$this->registry->set(str_replace('/', '_', $driver), new $class());
		} else {
			trigger_error('Error: Could not load database ' . $driver . '!');
			exit();
		}
	}

	public function config($config) {
		$this->config->load($config);
	}

	public function language($language) {
		return $this->language->load($language);
	}
}
