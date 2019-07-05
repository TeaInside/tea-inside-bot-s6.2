<?php

namespace TeaBot\Plugins\Quran;

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
	 * @param int $surat
	 * @param int $ayat
	 */
	public function __construct(int $surat, int $ayat = -1)
	{
		$this->surat = $surat;
		$this->ayat = $ayat;

		loadConfig("quran");
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

		return true;
	}
}
