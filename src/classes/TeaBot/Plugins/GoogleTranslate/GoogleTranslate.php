<?php

namespace TeaBot\Plugins\GoogleTranslate;

use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @version 0.0.1
 * @license MIT
 */
class GoogleTranslate
{

	const AVAILABLE_LANGUAGES = ["ja", "en", "id", "auto", "af", "sq", "am", "ar", "hy", "az", "eu", "be", "bn", "bs", "bg", "ca", "ceb", "ny", "zh-CN", "co", "hr", "cs", "da", "nl", "en", "eo", "et", "tl", "fi", "fr", "fy", "gl", "ka", "de", "el", "gu", "ht", "ha", "haw", "iw", "hi", "hmn", "hu", "is", "ig", "id", "ga", "it", "ja", "jw", "kn", "kk", "km", "ko", "ku", "ky", "lo", "la", "lv", "lt", "lb", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mn", "my", "ne", "no", "ps", "fa", "pl", "pt", "pa", "ro", "ru", "sm", "gd", "sr", "st", "sn", "sd", "si", "sk", "sl", "so", "es", "su", "sw", "sv", "tg", "ta", "te", "th", "tr", "uk", "ur", "uz", "vi", "cy", "xh", "yi", "yo", "zu", "en"];

	/**
	 * @var string
	 */
	private $source;

	/**
	 * @var string
	 */
	private $to;

	/**
	 * @var string
	 */
	private $string;

	/**
	 * @param string $source (Source Language)
	 * @param string $to	 (To Language)
	 * @param string $string (Text to be translated)
	 * @throws \Exception
	 */
	public function __construct(string $source, string $to, string $string)
	{
		$this->source = strtolower($source);
		$this->to = strtolower($to);
		$this->string = trim($string);

		if (array_search($this->source, self::AVAILABLE_LANGUAGES) === false) {
			throw new Exception("Invalid language: {$this->source}");
		}

		if (array_search($this->to, self::AVAILABLE_LANGUAGES) === false) {
			throw new Exception("Invalid language: {$this->to}");
		}
	}

	/**
	 * @return ?string	 
	 */
	public function execute(): ?string
	{
		$o = self::curl(
			"https://translate.google.com/m?hl=en&sl={$this->source}&tl={$this->to}&ie=UTF-8&prev=_m&q=".urlencode($this->string),
			[
				CURLOPT_USERAGENT => "Opera/9.80 (J2ME/MIDP; Opera Mini/7.1.32052/29.3709; U; en) Presto/2.8.119 Version/11.10"
			]
		);

		$o = $o["out"];

		if (preg_match("/<div dir=\"ltr\" class=\"t0\">(.+?)</", $o, $m)) {
			$result = trim(html_entity_decode($m[1], ENT_QUOTES, "UTF-8"));

			// Get romanji if exists
			if (preg_match("/<div dir=\"ltr\" class=\"o1\">(.+?)</", $o, $m)) {
				$result .= "\n(".html_entity_decode($m[1], ENT_QUOTES, "UTF-8").")";
			}
			return $result;
		}
	}

	/**
	 * @param string $url
	 * @param array  $opt
	 * @return array
	 */
	public static function curl(string $url, array $opt = []): array
	{
		$ch = curl_init($url);
		$optf = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		];
		foreach ($opt as $k => $v) {
			$optf[$k] = $v;
		}
		curl_setopt_array($ch, $optf);
		$o = curl_exec($ch);
		$err = curl_error($ch);
		$ern = curl_errno($ch);
		return [
			"out" => $o,
			"err" => $err,
			"ern" => $ern
		];
	}
}