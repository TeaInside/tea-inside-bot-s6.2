<?php

namespace TeaBot;

use Exception;

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
		
	}
}
