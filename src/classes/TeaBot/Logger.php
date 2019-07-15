<?php

namespace TeaBot;

use TeaBot\Loggers\GroupLogger;
use TeaBot\Loggers\PrivateLogger;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Logger
{
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
	 * @return void
	 */
	public function run(): void
	{
		$catch = ["text"];
		if (in_array($this->data["msg_type"], $catch)) {
			if ($this->data["chat_type"] === "group") {
				$logger = new GroupLogger($this->data);
			} else if ($this->data["chat_type"] === "private") {
				$logger = new PrivateLogger($this->data);
			}

			if (isset($logger)) {
				$this->execLogger($logger);
			}
		}
	}

	/**
	 * @param \TeaBot\LoggerFoundation $logger
	 * @return void
	 */
	private function execLogger(LoggerFoundation $logger): void
	{
		$logger->run();

		if ($this->data["msg_type"] === "text") {
			$logger->logText();
		}
	}
}
