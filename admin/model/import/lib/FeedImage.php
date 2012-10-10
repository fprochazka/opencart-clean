<?php

use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FeedImage extends Nette\Object
{

	/**
	 * @var string
	 */
	public $source;

	/**
	 * @var string
	 */
	public $title;



	/**
	 * @param string $source
	 * @param string $title
	 */
	public function __construct($source, $title = NULL)
	{
		$this->source = $source;
		$this->title = $title;
	}



	/**
	 * @param $directory
	 * @param $manufacturerId
	 * @param $product
	 *
	 * @return string
	 */
	public function download($directory, $manufacturerId, $product)
	{
		$extension = pathinfo($this->source, PATHINFO_EXTENSION);
		$parts = array_filter(array($manufacturerId, $product, $this->title));
		$imageName = 'feed_' . implode('.', array_map('Nette\Utils\Strings::webalize', $parts)) . '.' . $extension;

		$try = 3;
		$src = new RemoteFile($this->source);
		$targetPath = $directory . '/' . $imageName;

		do {
			if (!file_exists($targetPath)) {
				$src->copy($targetPath);
			}

			if (FALSE === @getimagesize($targetPath)) {
				@unlink($targetPath);

			} else {
				$isImage = TRUE;
				break;
			}

		} while($try--);

		if (isset($isImage)) {
			return $imageName;
		}

		Nette\Diagnostics\Debugger::log("Source $this->source is broken.", 'feed');
		return NULL;
	}

}
