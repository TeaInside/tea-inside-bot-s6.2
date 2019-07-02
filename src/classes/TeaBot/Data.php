<?php

namespace TeaBot;

use ArrayAccess;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Data implements ArrayAccess
{
	/**
	 * @var array
	 */
	public $in;

	/**
	 * @var array
	 */
	public $container;

	/**
	 * @param array $data
	 *
	 * Constructor.
	 */
	public function __construct(array $data)
	{
		$this->in = $data;
		$this->container["in"] = &$this->in;
		var_dump($data);
		$this->container["msg_id"] = &$this->in["message"]["message_id"];
		$this->container["chat_id"] = &$this->in["message"]["chat"]["id"];
	}

	/**
	 * @param mixed $key
	 * @return &mixed
	 */
	public function &offsetGet($key)
	{
		if (!array_key_exists($key, $this->container)) {
			$this->container[$key] = null;
		}
		return $this->container[$key];
	}

	/**
	 * @param mixed $key
	 * @param mixed &$data
	 * @return void
	 */
	public function offsetSet($key, $data)
	{
		$this->container[$key] = $data;
	}

	/**
	 * @param mixed $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->container);
	}

	/**
	 * @param mixed $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->container[$key]);
	}
}
