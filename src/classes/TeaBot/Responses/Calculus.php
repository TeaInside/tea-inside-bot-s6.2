<?php

namespace TeaBot\Responses;

use stdClass;
use TeaBot\Exe;
use TeaBot\Data;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;
use TeaBot\Plugins\Tex2Png\Tex2Png;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Calculus extends ResponseFoundation
{
	/**
	 * @var string
	 */
	private $token;

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		parent::__construct($data);
		loadConfig("calculus");
	}

	/**
	 * @param string $expr
	 * @return ?array
	 */
	public function execute(string $expr): ?array
	{
		return json_decode(
			file_get_contents("https://api.teainside.org/teacalc2.php?key=".CALCULUS_API_KEY."&expr=".urlencode($expr)),
			true
		);
	}

	/**
	 * @param string $expr
	 * @return bool
	 */
	public function c001(string $expr): bool
	{
		$res = $this->execute($expr);
		if (isset($res["solutions"][0]["entire_result"])) {
			$reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
		} else {
			$reply = isset($res["errorMessage"]) ? $res["errorMessage"] : "Couldn't get the result";
		}

		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => $reply
			]
		);

		return true;
	}

	/**
	 * @param string $expr
	 * @return bool
	 */
	public function c002(string $expr): bool
	{
		$res = $this->execute($expr);

		$photo = null;
		if (isset($res["solutions"][0]["entire_result"])) {
			$reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
			$photo = "https://api.teainside.org/latex_x.php?exp=".urlencode($reply);
		} else {
			$reply = isset($res["errorMessage"]) ? $res["errorMessage"] : "Couldn't get the result";
		}

		if (isset($photo)) {
			Exe::sendPhoto(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"photo" => $photo,
					"caption" => "<pre>".htmlspecialchars($r, ENT_QUOTES, "UTF-8")."</pre>",
					"parse_mode" => "html"
				]
			);
		} else {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => $reply
				]
			);
		}

		return true;
	}
}
