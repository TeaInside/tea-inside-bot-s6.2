<?php

namespace TeaBot\Loggers;

use DB;
use PDO;
use TeaBot\Exe;
use TeaBot\LoggerFoundation;
use TeaBot\Contracts\LoggerInterface;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot\Loggers
 * @version 6.2.0
 */
final class GroupLogger extends LoggerFoundation implements LoggerInterface
{
	/**
	 * @var string
	 */
	public $groupHash;

	/**
	 * @return void
	 */
	public function run(): void
	{
		$this->groupHash = sha1($this->data["group_id"]);
		while (static::groupIsLocked($this->groupHash)) {
			sleep(2);
		}
		static::groupLock($this->groupHash);

		/**
		 * @see TeaBot\LoggerFoundation
		 *
		 * 1 means group logger
		 */
		$this->userLogger($this->data, 1);
	}

	/**
	 * @return void
	 */
	public function logText(): void
	{
	}

	/**
	 * @return void
	 */
	public function logPhoto(): void
	{
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		static::groupUnlock($this->groupHash);
	}
}
