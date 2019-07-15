<?php

namespace TeaBot;

use DB;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
abstract class LoggerFoundation
{
	/**
	 * @var \TeaBot\Data
	 */
	protected $data;

	/**
	 * @var \PDO
	 */

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		$this->data = &$data;
		$this->pdo = DB::pdo();
	}

	/**
	 * @return void
	 */
	abstract public function run(): void;

	/**
	 * @return void
	 */
	abstract public function saveUserInfo(): void;
}
