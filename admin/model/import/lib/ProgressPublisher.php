<?php



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ProgressPublisher extends Nette\Object
{

	/**
	 * @var int
	 */
	protected $total = 100;

	/**
	 * @var string
	 */
	private $file;



	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file = $file;

		foreach (glob(__DIR__ . '/progress/*.json') as $existing) {
			@unlink($existing);
		}
	}



	/**
	 * @param int $total
	 *
	 * @return \ProgressPublisher
	 */
	public function setTotal($total)
	{
		$this->total = $total;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getFile()
	{
		return __DIR__ . '/../progress/' . $this->file . '.json';
	}



	/**
	 * @param integer $doneCount
	 */
	public function publish($doneCount)
	{
		file_put_contents($this->getFile(), json_encode(array(
			'total' => $this->total,
			'done' => $doneCount,
			'percents' => floor(($doneCount / $this->total) * 100)
		)));
	}



	/**
	 *
	 */
	public function finish()
	{
		$this->publish($this->total);
	}

}
