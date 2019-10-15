<?php

namespace TeaBot\Responses;

use stdClass;
use TeaBot\Exe;
use TeaBot\Data;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Calculus extends ResponseFoundation
{
	/**
	 * @const string
	 */
	private const API_KEY = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3d3dy5zeW1ib2xhYi5jb20iLCJleHAiOjE1NzEyMzIyMjh9.Ryb50DVDZPiudwVlEy4oUCHgf1Tz3wKHDHlBuvC0CbU";

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		parent::__construct($data);
	}

	/**
	 * @param string $expression
	 * @return bool
	 */
	public function simple(string $expression): bool
	{
		$expression = urlencode($expression);

		$o = self::curl(
			"https://www.symbolab.com/pub_api/steps?userId=fe&query={$expression}&language=en&subscribed=false&plotRequest=PlotOptional",
			[CURLOPT_HTTPHEADER => ["Authorization: Bearer ".(self::API_KEY)]]
		);

		if ($o["ern"]) {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "An error occured: {$o["ern"]}: {$o["err"]}"
				]
			);
			goto ret;
		}

		var_dump($o["out"]);

		$res = json_decode($o["out"], true);

		if (isset($res["solutions"][0]["entire_result"])) {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"]
				]
			);
		} else {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "Couldn't get the result"
				]
			);
		}

		ret:
		return true;
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
			CURLOPT_SSL_VERIFYHOST => false
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
