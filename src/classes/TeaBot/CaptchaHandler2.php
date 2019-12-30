<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class CaptchaHandler2
{
    const CAPTCHA_DIR = "/tmp/telegram/captcha_handler";

    /**
     * @var \TeaBot\Data
     */
    public $data;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $welcomeMessages = [];

    /**
     * @param \TeaBot\Data
     *
     * Constructor.
     */
    public function __construct(Data $data, string $type, array $welcomeMessages)
    {
        $this->data = $data;
        $this->type = $type;
        $this->welcomeMessages = $welcomeMessages;
        is_dir(self::CAPTCHA_DIR) or mkdir(self::CAPTCHA_DIR);
    }

    /**
     * @return void
     */
    public function run(): void
    {
        switch ($this->type) {
            case "calculus2":
                $this->calculusCaptcha();
                break;
            
            default:
                break;
        }
    }

    /**
     * @return void
     */
    private function calculusCaptcha()
    {
        foreach ($this->data["new_chat_members"] as $v) {
            $sockData = [];
            $cdata = json_decode(file_get_contents("https://captcha.teainside.org/api.php?key=abc123&action=get_captcha&type=calculus"), true);

            $name = htmlspecialchars($v["first_name"].
                (isset($v["last_name"]) ? " ".$v["last_name"] : ""),
                ENT_QUOTES, "UTF-8");

            $mention = "<a href=\"tg://user?id={$v["id"]}\">{$name}</a>";
            $cdata["photo"] = "https://api.teainside.org/latex_x.php?border=200&d=400&exp="
                .urlencode($cdata["latex"]);

            if (isset($v["username"])) {
                $mention .= " (@".$v["username"].")";
            }

            $cdata["tg_msg"] = $mention.
                "\n<b>Please solve the following captcha problem to make sure you are a human or you will be kicked in {$cdata["est_time"]} seconds.</b>\n\n".
                $cdata["msg"];

            $sockData["captcha_msg_id"] = json_decode(Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "caption" => $cdata["tg_msg"],
                    "photo" => $cdata["photo"],
                    "parse_mode" => "HTML"
                ]
            )["out"], true)["result"]["message_id"];

            $sockData["user_id"] = $v["id"];
            $sockData["chat_id"] = $this->data["chat_id"];
            $sockData["join_msg_id"] = $this->data["msg_id"];
            $sockData["welcome_msg_id"] = $this->welcomeMessages[$v["id"]] ?? null;
            $sockData["banned_hash"] = sha1($cdata["correct_answer"]);
            $sockData["mention"] = $mention;
            $sockData["sleep"] = $cdata["est_time"];
        }
    }
}
