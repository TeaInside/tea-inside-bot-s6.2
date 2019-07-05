<?php

namespace TeaBot\Plugins\Quran;

use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot\Plugins\Quran
 * @version 6.2.0
 */
final class Quran
{
	/**
	 * @var int
	 */
	private $surat;

	/**
	 * @var int
	 */
	private $ayat;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @param int $surat
	 * @param int $ayat
	 */
	public function __construct(int $surat, int $ayat = -1)
	{
		$this->surat = $surat;
		$this->ayat = $ayat;

		loadConfig("quran");

		if (!defined("QURAN_STORAGE_PATH")) {
			throw new Exception("QURAN_STORAGE_PATH is not defined!");
		}

		is_dir(QURAN_STORAGE_PATH) or mkdir(QURAN_STORAGE_PATH);
		$this->file = QURAN_STORAGE_PATH."/".sprintf("%03d%03d.mp3", $this->surat, $this->ayat);
	}

	/**
	 * @return bool
	 */
	public function get(): bool
	{
		$retried = false;
		_start_curl:
		$ch = curl_init(
			sprintf(
				"http://www.everyayah.com/data/Salaah_AbdulRahman_Bukhatir_128kbps/%03d%03d.mp3",
				$this->surat,
				$this->ayat
			)
		);
		curl_setopt_array($ch,
			[
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => "KodingTeh: /Scrape-Ayat 'https://t.me/joinchat/DoZ0OUVFzpjUK5yFgSelUA'"
			]
		);
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);
		$errno = curl_errno($ch);
		curl_close($ch);

		if (!( ($info["http_code"] >= 200) && ($info["http_code"] <= 299) )) {
			if (!$retried) {
				$retried = true;
				goto _start_curl;
			}
			return false;
		}

		if ($error) {
			if (!$retried) {
				$retried = true;
				goto _start_curl;
			}
			return false;
		}

		$ret = (bool)file_put_contents($this->file, $out);

		if (!$ret) {
			@unlink($this->file);
		}

		return $ret;
	}

	/**
	 * @return string
	 */
	public function getFile(): string
	{
		return $this->file;
	}
}
