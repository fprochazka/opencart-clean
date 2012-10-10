<?php

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class XmlProductsFeed extends Nette\Object
{

	const SOURCE_URL = '';

	/**
	 * @var \ModelCatalogProduct
	 */
	protected $products;

	/**
	 * @var \ModelImportProducts
	 */
	protected $imports;



	/**
	 * @param \ModelImportProducts $imports
	 * @param \XmlFetcher $xmlFetcher
	 * @param \ProgressPublisher $progress
	 *
	 * @throws Nette\InvalidStateException
	 * @return array
	 */
	public function import(ModelImportProducts $imports, XmlFetcher $xmlFetcher, ProgressPublisher $progress)
	{
		if (!static::SOURCE_URL) {
			throw new Nette\InvalidStateException("Constant SOURCE_URL is not defined.");
		}

		$this->imports = $imports;
		$this->products = $imports->model_catalog_product;

		$xmlObject = $xmlFetcher->fetchXml(static::SOURCE_URL);
		for ($xmlObject->rewind(), $counter = 1; $xmlObject->valid(); $xmlObject->next(), $counter++) {
			$product = $this->processProduct($xmlObject->current());
			if (!$product instanceof CatalogProduct) {
				$method = get_called_class() . '::processProduct()';
				throw new Nette\UnexpectedValueException("Object returned from $method is not instance of CatalogProduct.");
			}

			$product->allowProduct();
			$product->save($this->products);

			if ($counter % 10 === 0) {
				$progress->publish($xmlObject->getProgress());
			}
		}
	}



	/**
	 * @param \XmlObject|\stdClass $item
	 *
	 * @return \CatalogProduct
	 */
	protected function processProduct(XmlObject $item)
	{
	}



	/**
	 * @return array
	 */
	abstract function getManufacturers();

}
