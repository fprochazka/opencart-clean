<?php

require_once __DIR__ . '/extension_base.php';

class ControllerCommonContentBottom extends ControllerCommonExtensionBase
{

	public function index()
	{
		$this->renderContainer('content_bottom');
	}

}
