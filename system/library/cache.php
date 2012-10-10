<?php

use Nette\Diagnostics\Helpers;
use Nette\Diagnostics\Debugger;



class Cache
{
	/**
	 * @var int
	 */
	private $expire = 3600;

	/**
	 * @var array
	 */
	private $memory = array();

	/**
	 * @var int
	 */
	public $storeId = NULL;

	/**
	 * @var \CachePanel
	 */
	private $logger;



	/**
	 * @param \Config $config
	 */
  	public function __construct()
	{
		if ($files = glob(DIR_CACHE . 'cache.*')) {
			foreach ($files as $file) {
				$time = substr(strrchr($file, '.'), 1);

      			if ($time < time()) {
					if (file_exists($file)) {
						@unlink($file);
					}
      			}
    		}
		}

		$this->logger = new CachePanel($this);
		Debugger::$bar->addPanel($this->logger);
  	}



	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$call = $this->logger->gettingKey($key);

		if (isset($this->memory[$key])) {
			$this->logger->returnedValue($this->memory[$key], $call);
			return unserialize($this->memory[$key]);
		}

		$storeId = $this->storeId !== NULL ? '.s' . $this->storeId : NULL;
		if ($files = glob(DIR_CACHE . "cache{$storeId}." . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*')) {
			$this->memory[$key] = file_get_contents($files[0]);
			$this->logger->returnedValue($this->memory[$key], $call);
			return unserialize($this->memory[$key]);
		}
	}



	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
    	$this->delete($key);

		$storeId = $this->storeId !== NULL ? '.s' . $this->storeId : NULL;
		$file = DIR_CACHE . "cache{$storeId}." . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);
		file_put_contents($file, $this->memory[$key] = serialize($value));
  	}



	/**
	 * @param string $key
	 */
	public function delete($key)
	{
		$storeId = $this->storeId !== NULL ? '.s' . $this->storeId : NULL;
		foreach (func_get_args() as $key) {
			if (!$files = glob(DIR_CACHE . "cache{$storeId}.*" . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '*.*')) {
				continue;
			}

			foreach ($files as $file) {
				@unlink($file);
			}

			foreach ($this->memory as $storedKey => $value) {
				if (Nette\Utils\Strings::contains($storedKey, $key)) {
					unset ($this->memory[$storedKey]);
				}
			}
		}
  	}



	/**
	 * @return array
	 */
	public function getMemory()
	{
		return $this->memory;
	}

}


class CachePanel extends Nette\Object implements \Nette\Diagnostics\IBarPanel
{

	/**
	 * @var \Cache
	 */
	private $cache;

	/**
	 * @var array
	 */
	private $calls = array();



	/**
	 * @param \Cache $cache
	 */
	public function __construct(Cache $cache)
	{
		$this->cache = $cache;
	}



	/**
	 * @param string $key
	 * @return \Nette\ArrayHash
	 */
	public function gettingKey($key)
	{
		if (Debugger::$productionMode) {
			return;
		}

		if (($trace = debug_backtrace()) && isset($trace[1])) {
			return Nette\ArrayHash::from(array(
				'caller' => Helpers::editorLink($trace[1]['file'], $trace[1]['line']),
				'key' => $key,
				'value' => NULL,
			));
		}
	}



	/**
	 * @param mixed $value
	 * @param \Nette\ArrayHash $call
	 */
	public function returnedValue($value, Nette\ArrayHash $call = NULL)
	{
		if (Debugger::$productionMode) {
			return;
		}

		if (is_string($value) && FALSE !== ($value = unserialize($value)) && NULL !== $value) {
			$call->value = $value;
			$this->calls[$call->key][] = $call;
		}
	}



	/**
	 * @return string
	 */
	public function getTab()
	{
		if (!$this->calls) {
			return NULL;
		}

		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC">Cache';
	}



	/**
	 * @return string
	 */
	public function getPanel()
	{
		$s = '<table>';
		$h = callback('Nette\Templating\Helpers::escapeHtml');
		foreach ($this->calls as $key => $calls) {
			$rows = count($calls);
			$s .= '<tr><th rowspan="' . $h($rows) . '" width="50">' . $h(str_replace('.', ' ', $key)) . '</th>';
			$callsString = array();
			foreach ($calls as $call) {
				if ($caller = $call->caller) {
					$dir = $caller->getText();
					$caller->setText(basename(dirname($dir)) . '/' . basename($dir));
				}
				$callsString[] = "<td>" . Helpers::clickableDump($call->value, TRUE) . "</td><td>" . $caller . '</td>';
			}
			$s .= implode('</tr><tr>', $callsString);
			$s .= '</tr>';
		}
		$s .= '</table>';

		return '<h1>Cache lookups</h1><div class="nette-inner">' . $s . '</div>';
	}

}
