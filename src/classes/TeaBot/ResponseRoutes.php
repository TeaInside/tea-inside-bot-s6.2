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
		if (preg_match("/^(\/|\!|\~|\.)start$/Usi", $this->data["text"])) {
			if ($this->stExec(Responses\Start::class, "start")) {
				return true;
			}
		}

		/**
		 * Qur'an command.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)(?:quran )(\d{1,3}):(\d{1,3})$/Usi", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Quran::class, "quran", [(int)$m[1], (int)$m[2]])) {
				return true;
			}
		}

		/**
		 * Debug command.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)(?:debug)$/Usi", $this->data["text"])) {
			if ($this->stExec(Responses\Debug::class, "debug")) {
				return true;
			}
		}

		/**
		 * Jadwal Kuliah.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal\s+)(\d{2}\.\d{2}\.\d{4})(?:.)(\d+)/", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom::class, "jadwal", [$m[1], $m[2]])) {
				return true;
			}
		}

		/**
		 * Jadwal Kuliah.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal\s+)(senin|selasa|rabu|kamis|jum'?at|sabtu)(?:\s+)(\d{2}\.\d{2}\.\d{4})(?:.)(\d+)/i", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom::class, "jadwal", [$m[2], $m[3], $m[1]])) {
				return true;
			}
		}

		return false;
	}
}
