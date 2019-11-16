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
		 * Login AMIKOM.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:amikom\s+login\s+)(\S+)(?:\s+)(\S+)$/i", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom\Mahasiswa::class, "login", [$m[1], $m[2]])) {
				return true;
			}
		}

		/**
		 * Jadwal Kuliah.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal)$/i", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom\Mahasiswa::class, "jadwal")) {
				return true;
			}
		}

		/**
		 * Jadwal Kuliah.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal\s+)(senin|selasa|rabu|kamis|(jum')?at|sabtu)$/i", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom\Mahasiswa::class, "jadwal", [$m[1]])) {
				return true;
			}
		}

		/**
		 * Absen/Presensi
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:absen|presensi)(?:\s+)(.+?)$/i", $this->data["text"], $m)) {
			if ($this->stExec(Responses\Amikom\Mahasiswa::class, "presensi", [$m[1]])) {
				return true;
			}
		}

		/**
		 * Google translate.
		 */
		if (preg_match("/(?:\/|\!|\~|\.)?(?:tr)\s(\S+)\s(\S+)\s(.+)$/Usi", $this->data["text"], $m)) {
			if ($this->stExec(Responses\GoogleTranslate::class, "translate", [$m[1], $m[2], $m[3]])) {
				return true;
			}
		}

		/**
		 * Calculus.
		 */
		if (preg_match("/^(?:\/|\!|\~|\.)?(?:c)(\d{3})(?:(?:[\\s\\n])+)(.+?)$/si", $this->data["text"], $m)) {
			$m[2] = str_replace("\n", " ", $m[2]);
			$m[1] = (int)$m[1];
			switch ($m[1]) {
				case 1:
					if ($this->stExec(Responses\Calculus::class, "simple", [$m[2]])) {
						return true;
					}
				break;

				case 2:
					if ($this->stExec(Responses\Calculus::class, "simpleImg", [$m[2]])) {
						return true;
					}
				break;

				default:
				break;
			}
		}

		return false;
	}
}
