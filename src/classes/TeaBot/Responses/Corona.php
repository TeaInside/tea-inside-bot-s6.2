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

        $ch = curl_init("https://api.teainside.org/corona/");
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]
        );
        $o = json_decode(curl_exec($ch), true);
        $r = "[Coronavirus vt (global)]\nscraped_at: ".date("Y-m-d H:i:s", $o["scraped_at"])." (GMT +0)\n";
        $sdt = $o["sdt"];
        $cmt = $o["cmt"];
        $fst = $o["fst"];
        $r .= "sdt: ".$sdt."\nfst: ".$fst."\ncmt: ".$cmt."\n";
        $r .= "percent fst: ".number_format($fst/$cmt * 100, 5)." %\n";
        $r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 5)."\n";
        $r .= "pt: ".number_format(($sdt*$fst*$cmt)/3, 5)."\n";

        Exe::editMessageText(
            [
                "message_id" => $msgId,
                "chat_id" => $this->data["chat_id"],
                "text" => $r
            ]
        );

        return true;
    }

    /**
     * @return bool
     */
    public function checkCountry(string $country): bool
    {
        $msgId = json_decode(Exe::sendMessage(
            [
                "chat_id" => $this->data["chat_id"],
                "text" => "Collecting data...",
                "reply_to_message_id" => $this->data["msg_id"]
            ]
        )["out"], true)["result"]["message_id"];

        $ch = curl_init("https://api.teainside.org/corona/?country=".urlencode($country));
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]
        );
        $o = json_decode(curl_exec($ch), true);
        $r = "[Coronavirus vt ({$country} only)]\nscraped_at: ".date("Y-m-d H:i:s", $o["scraped_at"])." (GMT +0)\n";
        $sdt = $o["sdt"];
        $cmt = $o["cmt"];
        $fst = $o["fst"];
        $r .= "sdt: ".$sdt."\nfst: ".$fst."\ncmt: ".$cmt."\n";
        $r .= "percent fst: ".number_format($fst/$cmt * 100, 5)." %\n";
        $r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 5)."\n";
        $r .= "pt: ".number_format(($sdt*$fst*$cmt)/3, 5)."\n";
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
