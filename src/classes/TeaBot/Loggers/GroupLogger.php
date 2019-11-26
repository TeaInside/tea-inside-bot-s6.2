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
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		static::groupUnlock($this->groupHash);
	}
}
