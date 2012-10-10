<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */



/**
 * @param string $func
 * @return bool
 */
function isAvailable($func)
{
	if (@ini_get('safe_mode')) {
		return FALSE;
	}

	if ($disabled = @ini_get('disable_functions')) {
		$disabled = explode(',', $disabled);
		$disabled = array_map('trim', $disabled);
		return !in_array($func, $disabled);
	}

	return $disabled !== FALSE;
}
