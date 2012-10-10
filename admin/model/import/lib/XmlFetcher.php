<?php



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class XmlFetcher extends Nette\Object
{

	/**
	 * @var array
	 */
	private $downloaded = array();



	/**
	 * @param string $path
	 *
	 * @throws \Nette\InvalidStateException
	 * @return \XmlReaderIterator
	 */
	public function fetchXml($path)
	{
		$xml = new RemoteFile($path);
		$this->downloaded[] = $targetPath = DIR_DOWNLOAD . '/' . md5($path) . '.' . time() . '.xml';
		if (!$xml->copy($targetPath)) {
			throw new Nette\InvalidStateException("Xml feed could not be loaded");
		}

		return new XmlReaderIterator($targetPath);
	}



	/**
	 */
	public function __destruct()
	{
		foreach ($this->downloaded as $file) {
			@unlink($file);
		}
	}

}
