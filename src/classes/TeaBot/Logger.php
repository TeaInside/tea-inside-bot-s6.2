<?php

namespace TeaBot;

use Error;
use Exception;
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
		if ($this->data["event_type"] === Data::GENERAL_MSG) {
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
		try {
			$logger->run();
			switch ($this->data["msg_type"]) {
				case "text":
					$logger->logText();
				break;

				case "photo":
					$logger->logPhoto();
				break;

				default:
				break;
			}	
		} catch (Exception $e) {
			goto delete_lock_file;
		} catch (Error $e) {
			goto delete_lock_file;
		}

		return;

		delete_lock_file:
		// If error or exception is thrown, the program may not delete the lock file.
		if ($logger instanceof GroupLogger) {
			LoggerFoundation::groupUnlock($logger->groupHash);
		}
		throw $e;
	}
}
