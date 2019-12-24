<?php

header("Content-Type: application/json");

if ($_GET["key"] === "fd2554a4ea62d1804805b89b2ea823a0ea17980fd56fadcbadac2f8d791b") {
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
				if (file_exists($f) {
					@unlink($f);
				} else {
					@file_put_contents($f.".lock", time());
				}
				echo json_encode(["result" => "ok"]);
				exit;
			}
		break;
		
		default:
		break;
	}
}
http_response_code(400);
echo json_encode(["result" => "invalid parameter"]);
