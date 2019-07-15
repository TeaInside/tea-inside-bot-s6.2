<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
trait ResponseRoutes
{
	/**
	 * @return bool
	 */
	private function execRoutes(): bool
	{
		/**
		 * Start command.
		 */
		if (preg_match("/^(\/|\!|\~|\.)start$/i", $this->data["text"])) {
			if ($this->stExec(Responses\Start::class, "start")) {
				return true;
			}
		}

		/**
		 * Qur'an command.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)(?:quran )(\d{1,3}):(\d{1,3})$/", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Quran::class, "quran", [(int)$m[1], (int)$m[2]])) {
				return true;
			}	
		}

		return false;
	}
}
