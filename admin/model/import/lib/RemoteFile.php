<?php

use Nette\Diagnostics\Debugger;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RemoteFile extends Nette\Object
{

	/**
	 * @var string
	 */
	private $src;



	/**
	 * @param string $src
	 */
	public function __construct($src)
	{
		$this->src = $src;
	}



	/**
	 * @param string $targetPath
	 *
	 * @return bool
	 */
	public function copy($targetPath)
	{
		Debugger::tryError();
		if ($src = fopen($this->src, 'r')) {
			if (!$target = fopen($targetPath, 'w')) {
				throw new Nette\IOException("File $targetPath not writable.");
			}
			stream_copy_to_stream($src, $target);
			@fclose($target);
		}

		fclose($src);
		if (Debugger::catchError($error)) {
			if ($src) {
				@fclose($src);
			}
			if (isset($target) && $target) {
				@fclose($target);
			}
			Debugger::log($error);
			return FALSE;
		}

		@chmod($targetPath, 0777);
		return TRUE;
	}

}
