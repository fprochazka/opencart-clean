<?php

use Nette\Diagnostics\Debugger;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;


// libraries
foreach (glob(__DIR__ . '/lib/*.php') as $lib) {
	require_once $lib;
}


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property \Config $config
 * @property \Cache $cache
 * @property \ModelCatalogProduct $model_catalog_product
 * @property \ModelCatalogCategory $model_catalog_category
 * @property \ModelCatalogAttribute $model_catalog_attribute
 * @property \ModelCatalogOption $model_catalog_option
 * @property \ModelCatalogManufacturer $model_catalog_manufacturer
 * @property \ModelLocalisationLanguage $model_localisation_language
 * @property \ModelLocalisationLengthClass $model_localisation_length_class
 * @property \ModelLocalisationWeightClass $model_localisation_weight_class
 */
class ModelImportProducts extends Model
{

	/**
	 * @var array
	 */
	private static $feeds = array(
		'FunstormXmlFeed',
		'RepresentXmlFeed',
	);

	/**
	 * @var array
	 */
	private $feedInstances = array();

	/**
	 * @var array
	 */
	private $changelog;


	/**
	 * @var \ProgressPublisher
	 */
	private $progress;



	/**
	 * @param \Registry $registry
	 */
	public function __construct($registry)
	{
		parent::__construct($registry);
	}



	/**
	 * @return \XmlProductsFeed[]
	 */
	public function getAvailable()
	{
		if ($this->feedInstances) {
			return $this->feedInstances;
		}

		foreach (static::$feeds as $feedClass) {
			// create feed
			if (!class_exists($feedClass, false)) {
				require_once __DIR__ . "/feed/{$feedClass}.php";
			}

			$this->feedInstances[$feedClass] = new $feedClass();
		}

		return $this->feedInstances;
	}



	/**
	 * @param string $feedClassName
	 *
	 * @return bool
	 */
	public function import($feedClassName)
	{
		if (isAvailable('set_time_limit')) {
			@set_time_limit(0);
		} // let's just hope

		$this->changelog = array();
		foreach ($this->getAvailable() as $feedClass => $feed) {
			if (strtolower($feedClassName) !== strtolower($feedClass)) {
				continue;
			}

			/** @var \SystemContainer|\Nette\DI\Container $context */
			$context = Nette\Environment::getContext();
			/** @var \Nette\Database\Connection $connection */
			$connection = $context->getService('nette.database.default');
			$connectionCallbacks = $connection->onQuery;
			$connection->onQuery = array();

			try {
				foreach ($feed->getManufacturers() as $manufacturer) {
					if (!is_numeric($manufacturer)) {
						$manufacturer = $this->model_catalog_manufacturer->getNameId($manufacturer);
					}

					$this->model_catalog_product->disableAllFromManufacturer($manufacturer);
				}

				// load xml to common format and update products
				$feed->import($this, new XmlFetcher(), $this->progress);
				$this->progress->finish();

			} catch (Nette\InvalidStateException $e) {
				if (!Debugger::$productionMode) {
					throw $e;
				}

 				Debugger::log($e);
				$this->errorMessage("Nepodařilo se načíst $feedClass. " . $e->getMessage());
			}

			$connection->onQuery = $connectionCallbacks;
		}

		$this->cache->delete('product');

		return true;
	}



	/**
	 * @internal
	 * @param string $message
	 */
	public function successMessage($message)
	{
		$this->changelog[] = (object)array(
			'message' => $message,
			'type' => 'success'
		);
	}



	/**
	 * @internal
	 * @param string $message
	 */
	public function errorMessage($message)
	{
		$this->changelog[] = (object)array(
			'message' => $message,
			'type' => 'error'
		);
	}



	/**
	 * @return array
	 */
	public function changelog()
	{
		return $this->changelog;
	}



	/**
	 * @param \ProgressPublisher $progress
	 *
	 * @return \ModelImportProducts
	 */
	public function setProgressPublisher(ProgressPublisher $progress)
	{
		$this->progress = $progress;
		return $this;
	}

}
