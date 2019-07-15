<?php

namespace TeaBot\Contracts;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot\Contracts
 * @version 6.2.0
 */
interface LoggerInterface
{
	/**
	 * @param string
	 * @return void
	 */
	public function logText(): void;

	/**
	 * @param string
	 * @return void
	 */
	public function logPhoto(): void;
}
