<?php

header("Content-Type: application/json");

require __DIR__."/../bootstrap/autoload.php";

loadConfig("api");
loadConfig("telegram_bot");

if ($_GET["key"] === "chart" && isset($_GET["action"])) {
    header("Access-Control-Allow-Origin: *");
    switch ($_GET["action"]) {
        case "msg":
            if (isset($_GET["start_date"], $_GET["end_date"]) &&
                is_string($_GET["start_date"]) &&
                is_string($_GET["end_date"])
            ) {
                TeaBot\API\Chart\Chart::messages($_GET["start_date"], $_GET["end_date"]);
                exit;
            }
            break;
        case "user_stats":
            if (isset($_GET["start_date"], $_GET["end_date"]) &&
                is_string($_GET["start_date"]) &&
                is_string($_GET["end_date"])
            ) {
                TeaBot\API\Chart\UserStats::messages($_GET["start_date"], $_GET["end_date"]);
                exit;
            }
            break;
            break;
        default:
            break;
    }


    exit;
}

if ($_GET["key"] === SRABATSROBOT_API_KEY && isset($_GET["action"])) {
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
                $f = $dmsgdir."/".$msgId;
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
                        "text" => "Eligible ASL reply from @SrabatSrobot has been confirmed."
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
