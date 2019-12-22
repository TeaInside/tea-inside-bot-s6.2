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
     * @var string
     */
    public $deleteMsgHdir;

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
        $this->deleteMsgHdir = $this->captchaDir."/delete_msg_hash";
        is_dir("/tmp/telegram") or mkdir("/tmp/telegram");
        is_dir("/tmp/telegram/captcha_handler") or mkdir("/tmp/telegram/captcha_handler");
        is_dir($this->captchaDir) or mkdir($this->captchaDir);
        is_dir($this->deleteMsgHdir) or mkdir($this->deleteMsgHdir);
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
            $n = rand(1, 4);
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
                $cdata = json_decode(file_get_contents($fdc), true);
                if (isset($cdata["banned_hash"])) {
                    unlink("/tmp/telegram/calculus_lock/".$cdata["banned_hash"]);
                }
                $o = Exe::kickChatMember(
                    $x = [
                        "chat_id" => $this->data["chat_id"],
                        "user_id" => $v["id"]
                    ]
                );
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
                $this->clearDelQueue($this->data["chat_id"], $this->data["user_id"]);
                if (isset($this->welcomeMessages[$v["id"]])) {
                    $o = Exe::deleteMessage(
                        [
                            "chat_id" => $this->data["chat_id"],
                            "message_id" => $this->welcomeMessages[$v["id"]]
                        ]
                    );
                }
                Exe::unbanChatMember($x);
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
    public function handleIncomingMessage(Data $data): bool
    {
        $fdc = "/tmp/telegram/captcha_handler/{$data["chat_id"]}/{$data["user_id"]}";
        if (file_exists($fdc)) {
            $cdata = json_decode(file_get_contents($fdc), true);
            $captchaFile = BASEPATH."/src/captcha/{$cdata["type"]}/{$cdata["type"]}_".sprintf("%04d.php", $cdata["n"]);
            if (self::checkAnswer($captchaFile, $data["text"], $cdata["extra"] ?? null)) {

                posix_kill($cdata["pid"], SIGKILL);
                unlink($fdc);

                if (isset($cdata["banned_hash"])) {
                    unlink("/tmp/telegram/calculus_lock/".$cdata["banned_hash"]);
                }

                $name = htmlspecialchars(
                    $data["first_name"].(isset($data["last_name"]) ? " ".$data["last_name"] : ""),
                    ENT_QUOTES,
                    "UTF-8"
                );
                $mention = "<a href=\"tg://user?id={$data["id"]}\">{$name}</a>";
                if (isset($data["username"])) {
                    $mention .= " (@".$data["username"].")";
                }

                $o = json_decode(Exe::sendMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "text" => $mention." has answered the captcha correctly. Welcome to the group!",
                        "parse_mode" => "HTML",
                        "reply_to_message_id" => $data["msg_id"]
                    ]
                )["out"], true);
                $correctMsg = $o["result"]["message_id"];
                Exe::deleteMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "message_id" => $cdata["captcha_msg"]
                    ]
                );
                $this->clearDelQueue($data["chat_id"], $data["user_id"]);
                if (isset($cdata["welcome_msg"])) {
                    sleep(30);
                    $o = Exe::deleteMessage(
                        [
                            "chat_id" => $data["chat_id"],
                            "message_id" => $cdata["welcome_msg"]
                        ]
                    );
                }
                sleep(30);
                Exe::deleteMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "message_id" => $data["msg_id"]
                    ]
                );
                Exe::deleteMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "message_id" => $correctMsg
                    ]
                );
            } else {
                $this->msgDelQueue($data["chat_id"], $data["user_id"], $data["msg_id"]);
                $o = json_decode(Exe::sendMessage(
                    [
                        "chat_id" => $data["chat_id"],
                        "text" => "Wrong answer!",
                        "reply_to_message_id" => $data["msg_id"]
                    ]
                )["out"], true);
                $this->msgDelQueue($data["chat_id"], $data["user_id"], $o["result"]["message_id"]);
                file_put_contents($fdc, json_encode($cdata, JSON_UNESCAPED_SLASHES));
            }
            return true;
        }
        return false;
    }

    private function msgDelQueue($chatId, $userId, $msgId)
    {
        is_dir($this->deleteMsgHdir."/".$chatId) or mkdir($this->deleteMsgHdir."/".$chatId);
        is_dir($this->deleteMsgHdir."/".$chatId."/".$userId) or mkdir($this->deleteMsgHdir."/".$chatId."/".$userId);
        file_put_contents($this->deleteMsgHdir."/".$chatId."/".$userId."/".$msgId, time());
    }

    private function clearDelQueue($chatId, $userId)
    {
        $dir = $this->deleteMsgHdir."/".$chatId."/".$userId;
        if (is_dir($dir)) {
            $scan = scandir($dir);
            unset($scan[0], $scan[1]);
            $scan = array_reverse($scan);
            foreach ($scan as $msgId) {
                unlink($dir."/".$msgId);
                Exe::deleteMessage(
                    [
                        "chat_id" => $chatId,
                        "message_id" => $msgId
                    ]
                );
            }
            rmdir($dir);
        }
    }

    public static function checkAnswer(string $file, $answer = null, $extra = null): bool
    {
        $checkAnswer = true;
        return require $file;
    }
}