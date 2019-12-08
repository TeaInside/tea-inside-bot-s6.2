<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class CaptchaHandler
{
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
        $this->captchaDir = "/tmp/telegram/captcha_handler/{$this->data["chat_id"]}";
        is_dir("/tmp/telegram") or mkdir("/tmp/telegram");
        is_dir("/tmp/telegram/captcha_handler") or mkdir("/tmp/telegram/captcha_handler");
        is_dir($this->captchaDir) or mkdir($this->captchaDir);
    }

    /**
     * @return void
     */
    public function run(): void
    {
        switch ($this->type) {
            case "calculus":
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
        pcntl_signal(SIGCHLD, SIG_IGN);
        foreach ($this->data["new_chat_members"] as $v) {
            $n = rand(1, 2);
            $cdata = self::reqIsolate(BASEPATH."/src/captcha/calculus/calculus_".sprintf("%04d.php", $n));
            $cdata["n"] = $n;
            $cdata["type"] = "calculus";
            $name = htmlspecialchars(
                $v["first_name"].(isset($v["last_name"]) ? " ".$v["last_name"] : ""),
                ENT_QUOTES,
                "UTF-8"
            );
            $mention = "<a href=\"tg://user?id={$v["id"]}\">{$name}</a>";
            if (isset($v["username"])) {
                $mention .= " (@".$v["username"].")";
            }
            $cdata["msg"] = $mention."\n".$cdata["msg"];
            $captchaMsg = json_decode(Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "caption" => $cdata["msg"],
                    "photo" => $cdata["photo"],
                    "parse_mode" => "HTML"
                ]
            )["out"], true)["result"]["message_id"];

            $fdc = $this->captchaDir."/".$v["id"];
            $cdata["created_at"] = time();
            if (!($pid = pcntl_fork())) {
                cli_set_process_title("captcha-handler {$this->data["chat_id"]} {$v["id"]} ".json_encode($cdata));
                sleep($cdata["timeout"]);
                if (!file_exists($fdc)) {
                    exit;
                }
                $o = Exe::kickChatMember(
                    $x = [
                        "chat_id" => $this->data["chat_id"],
                        "user_id" => $v["id"]
                    ]
                );
                Exe::unbanChatMember($x);
                Exe::sendMessage(
                    [
                        "force_reply" => true,
                        "chat_id" => $this->data["chat_id"],
                        "text" => $mention." has been kicked from the group due to failed to answer the captcha.",
                        "parse_mode" => "HTML"
                    ]
                );
                unlink($fdc);
                Exe::deleteMessage(
                    [
                        "chat_id" => $this->data["chat_id"],
                        "message_id" =>  $captchaMsg
                    ]
                );
                if (isset($this->welcomeMessages[$v["id"]])) {
                    $o = Exe::deleteMessage(
                        [
                            "chat_id" => $this->data["chat_id"],
                            "message_id" => $this->welcomeMessages[$v["id"]]
                        ]
                    );
                }
                exit;
            }

            $cdata["pid"] = $pid;
            $cdata["captcha_msg"] = $captchaMsg;
            $cdata["welcome_msg"] = $this->welcomeMessages[$v["id"]] ?? null;
            if (file_exists($fdc)) {
                $ccdata = json_decode(file_get_contents($fdc), true);
                posix_kill($ccdata["pid"], SIGKILL);
                unlink($fdc);
            }
            file_put_contents($fdc, json_encode($cdata, JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * @param string $file
     * @return mixed
     */
    private static function reqIsolate(string $file)
    {
        return require $file;
    }

    /**
     * @param \TeaBot\Data $data
     * @return bool
     */
    public static function handleIncomingMessage(Data $data): bool
    {
        $fdc = "/tmp/telegram/captcha_handler/{$data["chat_id"]}/{$data["user_id"]}";
        if (file_exists($fdc)) {
            $cdata = json_decode(file_get_contents($fdc), true);
            $captchaFile = BASEPATH."/src/captcha/{$cdata["type"]}/{$cdata["type"]}_".sprintf("%04d.php", $cdata["n"]);
            if (self::checkAnswer($captchaFile, $data["text"], $cdata["extra"] ?? null)) {

                $name = htmlspecialchars(
                    $data["first_name"].(isset($data["last_name"]) ? " ".$data["last_name"] : ""),
                    ENT_QUOTES,
                    "UTF-8"
                );
                $mention = "<a href=\"tg://user?id={$data["id"]}\">{$name}</a>";
                if (isset($v["username"])) {
                    $mention .= " (@".$data["username"].")";
                }

                Exe::sendMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "text" => "{$mention} has answered the captcha correctly. Welcome to the group!",
                        "reply_to_message_id" => $data["msg_id"]
                    ]
                );
                posix_kill($cdata["pid"], SIGKILL);
                unlink($fdc);
                Exe::deleteMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "message_id" => $cdata["captcha_msg"]
                    ]
                );
                if (isset($cdata["welcome_msg"])) {
                    sleep(30);
                    $o = Exe::deleteMessage(
                        [
                            "chat_id" => $data["chat_id"],
                            "message_id" => $cdata["welcome_msg"]
                        ]
                    );
                    
                    echo $o["out"];
                    
                } else echo "no wel";
            } else {
                Exe::sendMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "text" => "Wrong answer!",
                        "reply_to_message_id" => $data["msg_id"]
                    ]
                );
            }
            return true;
        }
        return false;
    }

    public static function checkAnswer(string $file, $answer = null, $extra = null): bool
    {
        $checkAnswer = true;
        return require $file;
    }
}