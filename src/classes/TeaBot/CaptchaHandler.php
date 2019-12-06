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
     * @param \TeaBot\Data
     *
     * Constructor.
     */
    public function __construct(Data $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
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
            $n = rand(1, 1);
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
            Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "caption" => $cdata["msg"],
                    "photo" => $cdata["photo"],
                    "parse_mode" => "HTML"
                ]
            );
            $cdata["created_at"] = time();
            if (!($pid = pcntl_fork())) {
                cli_set_process_title("captcha-handler {$this->data["chat_id"]} {$v["id"]} ".json_encode($cdata));
                sleep($cdata["timeout"]);
                $o = Exe::kickChatMember(
                    $x = [
                        "chat_id" => $this->data["chat_id"],
                        "user_id" => $v["id"]
                    ]
                );
                Exe::unbanChatMember($x);
                Exe::sendMessage(
                    [
                        "chat_id" => $this->data["chat_id"],
                        "text" => $mention." has been kicked from the group due to failed to answer the captcha.\n\n{$o["out"]}",
                        "parse_mode" => "HTML"
                    ]
                );
                unlink($this->captchaDir."/".$v["id"]);
                exit;
            }
            $cdata["pid"] = $pid;
            file_put_contents($this->captchaDir."/".$v["id"], json_encode($cdata, JSON_UNESCAPED_SLASHES));
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
                Exe::sendMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "text" => "Captcha passed, you have answered the captcha correctly. Welcome to the group!",
                        "reply_to_message_id" => $data["msg_id"]
                    ]
                );
                shell_exec("/usr/bin/kill -9 {$cdata["pid"]}");
                unlink($fdc);
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
