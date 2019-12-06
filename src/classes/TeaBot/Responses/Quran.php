<?php

namespace TeaBot\Responses;

use CurlFile;
use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;
use TeaBot\Plugins\Quran\Quran as BaseQuran;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Quran extends ResponseFoundation
{
    /**
     * @param int $surat
     * @param int $ayat
     * @return bool
     */
    public function quran(int $surat, int $ayat = -1): bool
    {
        $st = new BaseQuran($surat, $ayat);
        if ($st->get()) {
            $st = $st->getFile();
            $o = Exe::execPost(
                "sendAudio",
                [
                    "audio" => new CurlFile($st),
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"]
                ]
            );
        } else {
            Exe::sendMessage(
                [
                    "text" => Lang::get("quran.not_found"),
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"]
                ]
            );
        }

        return true;
    }
}
