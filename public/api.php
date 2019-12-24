<?php

header("Content-Type: application/json");



if ($_GET["key"] === "fd2554a4ea62d1804805b89b2ea823a0ea17980fd56fadcbadac2f8d791b") {
	require __DIR__."/../bootstrap/autoload.php";
	loadConfig("telegram_bot");
	switch ($_GET["action"]) {
		case "asl_msg":
			if (isset($_GET["msg_id"], $_GET["chat_id"]) &&
				is_string($_GET["msg_id"]) &&
				is_string($_GET["chat_id"]) &&
				is_string($_GET["user_id"])
			) {
				$userId = $_GET["user_id"];
				$chatId = $_GET["chat_id"];
				$msgId = $_GET["msg_id"];
				$cdir = "/tmp/telegram/captcha_handler/{$chatId}";
				$dmsgdir = $cdir."/delete_msg_hash/{$chatId}/{$userId}";
				$f = $dmsgdir."/".$chatId."/".$userId."/".$msgId;
				if (file_exists($f)) {
					@unlink($f);
				} else {
					@file_put_contents($f.".lock", time());
				}
				unset($_GET["key"]);
				\TeaBot\Exe::sendMessage(
					[
						"chat_id" => $chatId,
						"reply_to_message_id" => $msgId,
						"text" => "Eligible ASL reply from @SrabatSrobot has been confirmed.\n\n".json_encode($_GET)."\n\n".$f
					]
				);
				echo json_encode(["result" => "ok"]);
				exit;
			}
		break;
	}
}
http_response_code(400);
echo json_encode(["result" => "invalid parameter"]);

// https://telegram-bot.teainside.org/api.php?key={key}&chat_id={xxx}&msg_id={yyy}&user_id={zzz}
// fd2554a4ea62d1804805b89b2ea823a0ea17980fd56fadcbadac2f8d791b