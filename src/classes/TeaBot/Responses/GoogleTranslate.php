<?php

namespace TeaBot\Responses;

use Exception;
use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;
use TeaBot\Plugins\GoogleTranslate\GoogleTranslate as GoogleTranslateBase;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class GoogleTranslate extends ResponseFoundation
{
    /**
     * @param string $source
     * @param string $to
     * @param string $text
     * @return bool
     */
    public function translate(string $source, string $to, string $text): bool
    {
        try {
            $text = str_replace("\n", "#aaa#", $text);
            $st = new GoogleTranslateBase($source, $to, $text);
            $res = str_replace(["# aaa #", "# Aaa #"], "\n", $st->execute());
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "text" => $res,
                    "reply_to_message_id" => $this->data["msg_id"],
                ]
            );
        } catch (Exception $e) {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "text" => $e->getMessage(),
                    "reply_to_message_id" => $this->data["msg_id"],
                ]
            );
        }
        return true;
    }
}
