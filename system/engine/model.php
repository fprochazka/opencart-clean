<?php


/**
 * @property \Loader $load
 * @property \Nette\DI\Container|\SystemContainer $nette
 * @property \Nette\Database\Connection $database
 * @property \Language $language
 * @property \DB $db
 * @property \Cache $cache
 * @property \Config $config
 */
abstract class Model extends Nette\Object
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
	 * @return object
	 */
	public function &__get($key)
	{
		/** @var \Loader $loader */
		$loader = $this->registry->get('load');
		$service = $loader->__get($key);
		return $service;
	}



	/**
	 * @param string $key
	 * @param object $value
	 */
	public function __set($key, $value)
	{
		$this->registry->set($key, $value);
	}
}
