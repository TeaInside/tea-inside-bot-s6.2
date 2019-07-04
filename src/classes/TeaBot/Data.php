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
		$this->in = $data;
		$this["in"] = &$this->in;
		if (isset($this->in["message"]["text"])) {

			if (isset($this->in["message"]["from"]["username"])) {
				$this["username"] = &$this->in["message"]["from"]["username"];
			} else {
				$this["username"] = null;
			}

			if (isset($this->in["message"]["from"]["language_code"])) {
				$this["lang"] = &$this->in["message"]["from"]["language_code"];
			} else {
				$this["lang"] = null;
			}

			$this["update_id"]	= &$this->in["update_id"];
			$this["text"]		= &$this->in["message"]["text"];
			$this["msg_id"]		= &$this->in["message"]["message_id"];
			$this["chat_id"]	= &$this->in["message"]["chat"]["id"];
			$this["chat_title"]	= &$this->in["message"]["chat"]["title"];
			$this["user_id"]	= &$this->in["message"]["from"]["id"];
			$this["is_bot"]		= &$this->in["message"]["from"]["is_bot"];
			$this["first_name"]	= &$this->in["message"]["from"]["first_name"];
			$this["data"]		= &$this->in["message"]["date"];
			$this["msg_type"]	= "text";

			if (isset($this->in["message"]["from"]["last_name"])) {
				$this["last_name"] = &$this->in["message"]["from"]["last_name"];	
			} else {
				$this["last_name"] = null;
			}

			if (isset($this->in["message"]["entities"])) {
				$this["entities"] = &$this->in["message"]["entities"];
			} else {
				$this["entities"] = null;
			}

			if ($this->in["message"]["chat"]["type"] === "private") {
				$this["chat_type"] = "private";
			} else {
				$this["chat_type"] = "group";
			}
		}
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
	public function offsetExists($key): bool
	{
		return isset($this->container[$key]);
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
