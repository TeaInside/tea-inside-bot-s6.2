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
            '/<h1>Recovered:<\/h1> <div class="maincounter-number" style="color:#8ACA2B "> <span>([^\>\<]+?)</Usi',
            $o, $m
        )) {
            $sdt = (int)str_replace(",", "", $m[1]);
            $r .= "sdt: ".trim($m[1])."\n";
        }

        if (preg_match(
            '/<h1>Deaths:<\/h1> <div class="maincounter-number"> <span>([^\>\<]+?)</Usi',
            $o, $m
        )) {
            $fst = (int)str_replace(",", "", $m[1]);
            $r .= "fst: ".trim($m[1])."\n";
        }

        if (preg_match(
            '/<h1>Coronavirus Cases:<\/h1> <div class="maincounter-number"> <span style="color:#aaa">([^\>\<]+?)</Usi',
            $o, $m
        )) {
            $cmt = (int)str_replace(",", "", $m[1]);
            $r .= "cmt: ".trim($m[1])."\n";
        }

        $r .= "percent fst: ".number_format($fst/$cmt * 100, 15)." %\n";
        $r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 15)."\n";
        $r .= "pt: ".number_format(($sdt*$fst*$cmt), 15)."\n";

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
    public function check2(): bool
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

        $r = "[Coronavirus vt (China only)]\ndatetime: ".gmdate("Y-m-d H:i:s")." (GMT +0 qmq)\n";

        if (preg_match(
            '/China <\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)</Ui',
            $o, $m
        )) {
            $m[3] = trim($m[3]);
            $m[2] = trim($m[2]);
            $m[1] = trim($m[1]);
            $sdt = (int)str_replace(",", "", trim($m[3]));
            $r .= "sdt: ".$m[3]."\n";
            $fst = (int)str_replace(",", "", trim($m[2]));
            $r .= "fst: ".$m[2]."\n";
            $cmt = (int)str_replace(",", "", trim($m[1]));
            $r .= "cmt: ".$m[1]."\n";
        }

        $r .= "percent fst: ".number_format($fst/$cmt * 100, 15)." %\n";
        $r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 15)."\n";
        $r .= "pt: ".number_format(($sdt*$fst*$cmt), 15)."\n";

        Exe::editMessageText(
            [
                "message_id" => $msgId,
                "chat_id" => $this->data["chat_id"],
                "text" => $r
            ]
        );

        return true;
    }

    public function checkIndonesia(): bool
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

        $r = "[Coronavirus vt (Indonesia only)]\ndatetime: ".gmdate("Y-m-d H:i:s")." (GMT +0 qmq)\n";

        if (preg_match(
            '/Indonesia <\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)</Ui',
            $o, $m
        )) {
            $m[3] = trim($m[3]);
            $m[2] = trim($m[2]);
            $m[1] = trim($m[1]);

            $m[1] === "" and $m[1] = 0;
            $m[2] === "" and $m[2] = 0;
            $m[3] === "" and $m[3] = 0;

            $sdt = (int)str_replace(",", "", trim($m[3]));
            $r .= "sdt: ".$m[3]."\n";
            $fst = (int)str_replace(",", "", trim($m[2]));
            $r .= "fst: ".$m[2]."\n";
            $cmt = (int)str_replace(",", "", trim($m[1]));
            $r .= "cmt: ".$m[1]."\n";
        }

        $r .= "percent fst: ".number_format($fst/$cmt * 100, 15)." %\n";
        $r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 15)."\n";
        $r .= "pt: ".number_format(($sdt*$fst*$cmt), 15)."\n";

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

