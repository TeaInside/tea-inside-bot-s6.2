<?php

namespace TeaBot\Responses;

use TeaBot\Exe;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Start extends ResponseFoundation
{
	/**
	 * @return bool
	 */
	public function start(): bool
	{
		Exe::sendMessage(
			[
				"text" => "test",
				"chat_id" => $this->data["chat_id"]
			]
		);
		return true;
	}
}
