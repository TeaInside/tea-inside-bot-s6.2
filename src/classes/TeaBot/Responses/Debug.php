<?php

namespace TeaBot\Responses;

use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Debug extends ResponseFoundation
{
    /**
     * @return bool
     */
    public function debug(): bool
    {
        $debugData = json_encode($this->data->in, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        Exe::sendMessage(
            [
                "chat_id" => $this->data["chat_id"],
                "reply_to_message_id" => $this->data["msg_id"],
                "text" => "<pre>".htmlspecialchars($debugData)."</pre>",
                "parse_mode" => "HTML"
            ]
        );

        return true;
    }
}
