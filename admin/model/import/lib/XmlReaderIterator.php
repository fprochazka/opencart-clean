<?php




/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class XmlReaderIterator extends Nette\Object implements \Iterator
{

	/**
	 * @var \XMLReader
	 */
	private $xmlReader;

	/**
	 * @var string
	 */
	private $xmlFile;

	/**
	 * @var string
	 */
	private $itemName;

	/**
	 * @var int
	 */
	private $counter = 0;

	/**
	 * @var XmlObject
	 */
	private $current;

	/**
	 * @var integer
	 */
	private $size;

	/**
	 * @var integer
	 */
	private $read;



	/**
	 * @param string $xmlFile
	 */
	public function __construct($xmlFile)
	{
		$this->xmlFile = $xmlFile;
		$this->size = filesize($xmlFile);
	}



	/**
	 */
	public function rewind()
	{
		$this->__destruct();
		$this->xmlReader = new XMLReader();
		$this->xmlReader->open($this->xmlFile);
		$this->next();
	}



	/**
	 * @return \XmlObject
	 */
	public function current()
	{
		return $this->current;
	}



	/**
	 * @return int
	 */
	public function key()
	{
		return $this->counter;
	}



	/**
	 * @return \XmlObject
	 */
	public function next()
	{
		if (!$this->itemName) {
			$this->xmlReader->read(); // root element?
			do {
				$this->xmlReader->read(); // item
				$this->itemName = $this->xmlReader->name;

			} while ($this->itemName === '#text' && $this->itemName);
		}

		if ($this->current) {
			$this->xmlReader->next($this->itemName);
		}

		if ($element = $this->xmlReader->readOuterXML()) {
			$this->read += strlen($element);
			$this->current = new XmlObject($element);

		} else {
			$this->current = NULL;
		}

		$this->counter++;
		return $this->current;
	}



	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this->current !== NULL;
	}



	/**
	 */
	public function __destruct()
	{
		$this->counter = $this->read = 0;
		$this->current = NULL;
		if ($this->xmlReader) {
			$this->xmlReader->close();
		}
	}



	/**
	 * @return int
	 */
	public function getProgress()
	{
		return ($this->read / $this->size) * 100;
	}

}
