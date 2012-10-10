<?php

final class Registry
{

	/**
	 * @var array
	 */
	private $data = array();



	/**
	 * @param string $key
	 * @return object
	 */
	public function get($key)
	{
		return (isset($this->data[$key]) ? $this->data[$key] : $this->get('load')->__get($key));
	}



	/**
	 * @param string $key
	 * @param object $value
	 *
	 * @return object
	 */
	public function set($key, $value)
	{
		return $this->data[$key] = $value;
	}



	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
    	return isset($this->data[$key]);
  	}

}
