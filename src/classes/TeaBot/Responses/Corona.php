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
final class Corona extends ResponseFoundation
{
    /**
     * @return bool
     */
    public function check(): bool
    {
        $msgId = json_decode(Exe::sendMessage(
            [
                "chat_id" => $this->data["chat_id"],
                "text" => "Collecting data...",
                "reply_to_message_id" => $this->data["msg_id"]
            ]
        )["out"], true)["result"]["message_id"];

        $ch = curl_init("https://www.worldometers.info/coronavirus/");
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]
        );
        $o = curl_exec($ch);

        $sdt = $fst = $cmt = 0;

        $r = "[Coronavirus vt]\ndatetime: ".gmdate("Y-m-d H:i:s")." (GMT +0 qmq)\n";

        if (preg_match(
            '/<h1>Recovered:<\/h1> <div class="maincounter-number" style="color:#8ACA2B "> <span>(.+?)</Usi',
            $o, $m
        )) {
            $sdt = (int)str_replace(",", "", $m[1]);
            $r .= "sdt: ".$sdt."\n";
        }

        if (preg_match(
            '/<h1>Deaths:<\/h1> <div class="maincounter-number"> <span>(.+?)</Usi',
            $o, $m
        )) {
            $fst = (int)str_replace(",", "", $m[1]);
            $r .= "fst: ".$fst."\n";
        }

        if (preg_match(
            '/<h1>Coronavirus Cases:<\/h1> <div class="maincounter-number"> <span style="color:#aaa">(.+?)</Usi',
            $o, $m
        )) {
            $cmt = (int)str_replace(",", "", $m[1]);
            $r .= "cmt: ".$cmt."\n";
        }

        $r .= "percent fst: ".($fst/$cmt * 100)." %\n";
        $r .= "mean_total: ".(($sdt+$fst+$cmt)/3)."\n";
        $r .= "pt: ".(($sdt*$fst*$cmt)/3)."\n";

        Exe::editMessageText(
            [
                "message_id" => $msgId,
                "chat_id" => $this->data["chat_id"],
                "text" => $r
            ]
        );

        return true;
    }
}

