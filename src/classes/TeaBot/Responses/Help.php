<?php

namespace TeaBot\Responses;

use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Help extends ResponseFoundation
{
	/**
	 * @return bool
	 */
	public function help(): bool
	{
		if ($this->data["chat_type"] === "private") {
			Exe::sendMessage(
				[
					"text" => Lang::get("help.private"),
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"]
				]
			);
		} else {			
			$o = Exe::sendMessage(
				[
					"text" => Lang::get("help.group"),
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"]
				]
			);
		}
		return true;
	}
}
