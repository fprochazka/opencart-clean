<?php

require_once __DIR__ . '/extension_base.php';

class ControllerCommonColumnLeft extends ControllerCommonExtensionBase
{

	public function index()
	{
		$this->renderContainer('column_left');
	}

}
