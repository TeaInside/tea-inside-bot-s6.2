<?php

namespace TeaBot\Responses;

use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\Data;
use TeaBot\ResponseFoundation;
use TeaBot\Plugins\Paxel\Paxel as BasePaxel;

is_dir(STORAGE_PATH."/paxel") or mkdir(STORAGE_PATH."/paxel");
define("PAXEL_TG", STORAGE_PATH."/paxel/tg");
define("PAXEL_DIR", STORAGE_PATH."/paxel/base_paxel");

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Paxel extends ResponseFoundation
{

    /**
     * @param \TeaBot\Data &$data
     *
     * Constructor.
     */
    public function __construct(Data &$data)
    {
        parent::__construct($data);
        $this->ufile = PAXEL_TG."/".$this->data["user_id"];
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function loginPaxel(string $username, string $password): bool
    {
        $ucf = [
            "username" => $username,
            "password" => $password
        ];
        file_put_contents($this->ufile, json_encode($ucf));

        $px = new BasePaxel($username, $password);
        if ($px->login()) {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" =>
"Login success!

<b>First name:</b> {$px->userData["data"]["first_name"]}
<b>Last name:</b> {$px->userData["data"]["last_name"]}
<b>Phone:</b> {$px->userData["data"]["phone"]}
<b>Username:</b> {$px->userData["data"]["username"]}
<b>Status:</b> {$px->userData["data"]["status"]}",
                    "parse_mode" => "HTML"
                ]
            );
        } else {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Login failed!"
                ]
            );
        }

        return true;
    }
}
