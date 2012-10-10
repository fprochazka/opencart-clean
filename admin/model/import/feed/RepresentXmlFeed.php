<?php

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RepresentXmlFeed extends XmlProductsFeed
{
	const SOURCE_URL = 'http://www.represent.eu/xml/products/feedcz_01.xml';

	/**
	 * @var array
	 */
	protected static $parameterDict = array();



	/**
	 * @return array
	 */
	public function getManufacturers()
	{
		return array(
			'Represent'
		);
	}



	/**
	 * @param \XmlObject|\stdClass $item
	 *
	 * @return \CatalogProduct
	 */
	protected function processProduct(XmlObject $item)
	{
		$code = Strings::match((string)$item->CODE, '~^(?P<grouper>[^\\-]+\\-[^\\-]+\\-[0-9]{2})(.*)~');
		$product = $this->products->findByImport($code['grouper'], NULL);

		// details
		if (!$product->id) {
			$product->setDescription(
				$item->CATEGORY . ' ' . $item->MANUFACTURER . ' ' . $item->PRODUCT,
				$item->description
			);

			$product->addInfo(array(
				'model' => $code['grouper'],
				// 'price_original' => (string)$item->PRICE_ORIGINAL
			));
		}

		$product->addInfo(array(
			'manufacturer' => 'Represent',
			'price' => (float)str_replace(',', '.', (string)$item->PRICE),
		));

		// variants
		if ($size = (string)$item->SIZE) {
			$product->addVariants('size', array($size));
		}

		if ($color = (string)$item->COLOR) {
			$product->addVariants('color', array($color));
		}

		// images
		if ($image = (string)$item->IMGURL) {
			$product->addImage(new FeedImage($image, $color ? : NULL));
		}

		return $product;
	}


}
