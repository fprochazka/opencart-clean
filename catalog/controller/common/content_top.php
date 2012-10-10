<?php

require_once __DIR__ . '/extension_base.php';

class ControllerCommonContentTop extends ControllerCommonExtensionBase
{

	public function index()
	{
		$this->renderContainer('content_top');
	}

}
