<?php

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FunstormXmlFeed extends XmlProductsFeed
{
	const SOURCE_URL = 'http://www.funstorm-shop.cz/xml/feed_02.xml.php';

	/**
	 * @var array
	 */
	protected static $parameterDict = array(
		'Barva' => 'color',
		'Velikosti konfekce' => 'size'
	);



	/**
	 * @return array
	 */
	public function getManufacturers()
	{
		return array(
			'Funstorm Clothing'
		);
	}



	/**
	 * @param \XmlObject|\stdClass $item
	 * @return \CatalogProduct
	 */
	protected function processProduct(XmlObject $item)
	{
		$product = $this->products->findByImport($item->itemid, $item->catalognumber);

		// details
		if (!$product->id) {
			$nameParts = Strings::split((string)$item->name, '~\s+~');
			$model = array_pop($nameParts);
			$name = implode(' ', $nameParts);

			$product->setDescription($name, $item->description);
			$product->addInfo(array(
				'model' => $model,
			));
		}

		$product->addInfo(array(
			'manufacturer' => 'Funstorm Clothing',
			'price' => (float)str_replace(',', '.', (string)$item->price),
			// 'price_original' => (string)$item->pricecommon
		));

		// variants
		$product->option = array();
		foreach ($item->parameters as $parameter) {
			$product->addVariants(
				$this->variantName($parameter),
				$this->variants($parameter->values)
			);
		}

		// images
		foreach ($item->images as $image) {
			$product->addImage($this->image($image));
		}

		return $product;
	}



	/**
	 * @param \XmlObject|\stdClass $parameter
	 *
	 * @return string
	 */
	protected function variantName(XmlObject $parameter)
	{
		if (isset(static::$parameterDict[$parameterName = (string)$parameter->name])) {
			$parameterName = static::$parameterDict[$parameterName];
		}

		return $parameterName;
	}



	/**
	 * @param \XmlObject|\stdClass $parameter
	 * @return array
	 */
	protected function variants(XmlObject $parameter)
	{
		$list = array();
		foreach ($parameter as $variant) {
			$list[] = (string)$variant;
		}

		return $list;
	}



	/**
	 * @param \XmlObject|\stdClass $image
	 *
	 * @return array
	 */
	protected function image(XmlObject $image)
	{
		return new \FeedImage(
			(string)$image->src,
			(string)$image->color
		);
	}

}
