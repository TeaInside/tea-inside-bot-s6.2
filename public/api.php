<?php

require __DIR__."/../bootstrap/autoload.php";

loadConfig("telegram_bot");

use TeaBot\API\GetGroupMessages;

if (isset($_GET["action"], $_GET["key"]) && is_string($_GET["action"]) && is_string($_GET["key"])) {

	if ($_GET["key"] !== "e295c4659f3869f1c80129f28251ef11b30a4df1") {
		goto forbidden;
	}

	try {
		switch ($_GET["action"]) {
			case "get_group_messages":
				$st = new GetGroupMessages();
			break;
				
			default:
			break;
		}
		$st->dispatch();
		exit;	
	} catch (Exception $e) {
		goto error;
	}
}


forbidden:
header("Content-Type: application/json");
http_response_code(403);
print json_encode(
	[
		"error_code" => 403,
		"error_message" => "Unauthorized"
	]
);
exit;
