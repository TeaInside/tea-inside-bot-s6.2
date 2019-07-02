<?php

namespace TeaBot;

use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Response
{
	use ResponseRoutes;

	/**
	 * @var \TeaBot\Data
	 */
	private $data;

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		$this->data = &$data;
	}

	/**
	 * @param \TeaBot\ResponseFoundation
	 * @param string $method
	 * @param array  &$parameters
	 * @return bool
	 */
	private function stExec(string $class, string $method, array &$parameters = []): bool
	{
		return $this->internalStExec(new $class($this->data), $method, $parameters);
	}

	/**
	 * @param \TeaBot\ResponseFoundation
	 * @param string $method
	 * @param array  $parameters
	 * @return bool
	 */
	private function internalStExec(ResponseFoundation $obj, string $method, array &$parameters): bool
	{
		return (bool)$obj->{$method}(...$parameters);
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		if (isset($this->data["msg_type"])) {
			var_dump($this->data);
			if ($this->data["msg_type"] === "text") {
				$this->execRoutes();
			}
		}
	}
}
