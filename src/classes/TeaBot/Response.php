<?php

namespace TeaBot;

use DB;
use PDO;
use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Response
{
    use ResponseRoutes;

    /**
     * @var \TeaBot\Data
     */
    private $data;

    /**
     * @param \TeaBot\Data &$data
     *
     * Constructor.
     */
    public function __construct(Data &$data)
    {
        $this->data = &$data;
    }

    /**
     * @param \TeaBot\ResponseFoundation
     * @param string $method
     * @param array  &$parameters
     * @return bool
     */
    private function stExec(string $class, string $method, array $parameters = []): bool
    {
        return $this->internalStExec(new $class($this->data), $method, $parameters);
    }

    /**
     * @param \TeaBot\ResponseFoundation
     * @param string $method
     * @param array  $parameters
     * @return bool
     */
    private function internalStExec(ResponseFoundation $obj, string $method, array $parameters): bool
    {
        return (bool)$obj->{$method}(...$parameters);
    }

    /**
     * @return void
     */
    public function run(): void
    {
        if (isset($this->data["msg_type"])) {
            if (isset($this->data["text"])) {
                if (!CaptchaHandler2::havingCaptcha($this->data)) {
                    $this->execRoutes();
                }
            } else if (isset($this->data["new_chat_members"])) {
                $this->sendWelcome();
            } else if (isset($this->data["msg_id"])) {
                CaptchaHandler2::havingCaptcha($this->data);
            }
        }
    }

    /**
     * @return void
     */
    private function sendWelcome()
    {
        $pdo = DB::pdo();
        $st = $pdo->prepare("SELECT `welcome_msg`,`captcha` FROM `groups` WHERE `group_id` = :group_id LIMIT 1;");
        $st->execute([":group_id" => $this->data["chat_id"]]);
        $welcomeMessages = [];
        if ($r = $st->fetch(PDO::FETCH_NUM)) {
            if ($r[0]) {
                foreach ($this->data["new_chat_members"] as $v) {
                    $reply = str_replace(
                        [
                            "{{user_link}}",
                            "{{first_name}}",
                            "{{last_name}}",
                            "{{full_name}}",
                            "{{group_name}}",
                        ],
                        [
                            "tg://user?id=".$v["id"],
                            htmlspecialchars($v["first_name"], ENT_QUOTES, "UTF-8"),
                            htmlspecialchars($v["last_name"] ?? "", ENT_QUOTES, "UTF-8"),
                            htmlspecialchars($v["first_name"].(isset($v["last_name"])?" ".$v["last_name"] : ""), ENT_QUOTES, "UTF-8"),
                            htmlspecialchars($this->data->in["message"]["chat"]["title"], ENT_QUOTES, "UTF-8")
                        ],
                        $r[0]
                    );

                    $welcomeMessages[$v["id"]] = json_decode(Exe::sendMessage(
                        [
                            "chat_id" => $this->data["chat_id"],
                            "reply_to_message_id" => $this->data["msg_id"],
                            "text" => $reply,
                            "parse_mode" => "HTML"
                        ]
                    )["out"], true)["result"]["message_id"];

                    LoggerFoundation::userLogger(
                        [
                            "user_id" => $v["id"],
                            "username" => ($v["username"] ?? null),
                            "first_name" => $v["first_name"],
                            "last_name" => ($v["last_name"] ?? null),
                            "is_bot" => $v["is_bot"]
                        ],
                        0
                    );
                }
            }

            if ($r[1]) {
                if ($r[1] === "calculus2") {
                    (new CaptchaHandler2($this->data, $r[1], $welcomeMessages))->run();
                } else {
                    (new CaptchaHandler($this->data, $r[1], $welcomeMessages))->run();
                }
            }
        } else {
            foreach ($this->data["new_chat_members"] as $v) {
                LoggerFoundation::userLogger(
                    [
                        "user_id" => $v["id"],
                        "username" => ($v["username"] ?? null),
                        "first_name" => $v["first_name"],
                        "last_name" => ($v["last_name"] ?? null),
                        "is_bot" => $v["is_bot"]
                    ],
                    0
                );
            }
        }
    }
}
