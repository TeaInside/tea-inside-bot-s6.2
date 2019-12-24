<?php

namespace TeaBot\Responses;

use stdClass;
use TeaBot\Exe;
use TeaBot\Data;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;
use TeaBot\Plugins\Tex2Png\Tex2Png;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Calculus extends ResponseFoundation
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param \TeaBot\Data &$data
     *
     * Constructor.
     */
    public function __construct(Data &$data)
    {
        parent::__construct($data);
        loadConfig("calculus");
    }

    /**
     * @param string $expr
     * @return ?array
     */
    public function execute(string $expr): ?array
    {
        return json_decode(
            file_get_contents("https://api.teainside.org/teacalc2.php?key=".CALCULUS_API_KEY."&expr=".urlencode($expr)),
            true
        );
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function c001(string $expr): bool
    {
        if ($this->exprCheck($expr)) return true;

        $res = $this->execute($expr);
        if (isset($res["solutions"][0]["entire_result"])) {

            if ($this->exprCheck($res["solutions"][0]["entire_result"])) return true;

            if ($res["solutions"][0]["entire_result"][0] === "=") {
                $reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
            } else {
                $reply = "(".$res["dym"]["originalEquation"].") \\;\\Rightarrow\\; (".$res["solutions"][0]["entire_result"].")";
            }
        } else {
            $reply = isset($res["errorMessage"]) ? $res["errorMessage"] : "Couldn't get the result";
        }

        Exe::sendMessage(
            [
                "chat_id" => $this->data["chat_id"],
                "reply_to_message_id" => $this->data["msg_id"],
                "text" => $reply
            ]
        );

        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function c002(string $expr): bool
    {
        $res = $this->execute($expr);

        $photo = null;
        if (isset($res["solutions"][0]["entire_result"])) {

            if ($this->exprCheck($res["solutions"][0]["entire_result"])) return true;

            if ($res["solutions"][0]["entire_result"][0] === "=") {
                $reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
            } else {
                $reply = "(".$res["dym"]["originalEquation"].") \\;\\Rightarrow\\; (".$res["solutions"][0]["entire_result"].")";
            }
            $reply = str_replace(
                [
                    "\xe2\x88\x82"
                ],
                [
                    "\\partial"
                ],
                $reply
            );
            $photo = "https://api.teainside.org/latex_x.php?border=200&d=600&exp=".urlencode($reply);
        } else {
            $reply = isset($res["errorMessage"]) ? $res["errorMessage"] : "Couldn't get the result";
        }

        if (isset($photo)) {
            Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => $photo,
                    "caption" => "<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>",
                    "parse_mode" => "html"
                ]
            );
        } else {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => $reply
                ]
            );
        }

        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function cr02(string $expr): bool
    {
        if (preg_match('/^(\-?[\d\.]+)(?:\s*(\+|\-)\s*)(\-?[\d\.]*)(?:i\s*\;\s*)(\-?[\d\.]+)(?:\s*(\+|\-)\s*)(\-?[\d\.]*)i$/', $expr, $m)) {
            $reMin = (float)$m[1];
            $imMin = ($m[3] === "" ? 1 : (float)$m[3]) * ($m[2] == "-" ? -1 : 1);
            $reMax = (float)$m[4];
            $imMax = ($m[6] === "" ? 1 : (float)$m[6]) * ($m[5] == "-" ? -1 : 1);
            $hash = md5("q".$reMin.$reMax.$imMax.$imMin);

            $baseDir = BASEPATH."/storage/telegram/riemann_graph";

            if (file_exists($baseDir."/{$hash}.gif")) {
                goto send_photo;
            }

            is_dir($baseDir) or mkdir($baseDir);

            $oo = Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "text" => "Calculating...",
                    "reply_to_message_id" => $this->data["msg_id"]
                ]
            );

            $url = "http://mathworld.wolfram.com/webMathematica/ComplexPlots.jsp?name=RiemannZeta&zMin={$reMin}%2B{$imMin}*I&zMax={$reMax}%2B{$imMax}*I&nt=1";
            $ch = curl_init($url);
            curl_setopt_array($ch,
                [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_CONNECTTIMEOUT => 120
                ]
            );
            $o = curl_exec($ch);
            if ($err = curl_error($ch)) {
                $ern = curl_errno($ch);
                curl_close($ch);
                $j = json_decode($oo["out"], true);
                Exe::editMessageText(
                    [
                        "chat_id" => $this->data["chat_id"],
                        "message_id" => $j["result"]["message_id"],
                        "text" => "Error: ({$ern}) {$err}",
                    ]
                );
                return true;
            }
            curl_close($ch);
            file_put_contents($baseDir."/{$hash}.gif", $o);

            send_photo:
            $o = Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => "https://telegram-bot.teainside.org/storage/riemann_graph/{$hash}.gif",
                    "caption" => "<b>Re min, max:</b> {$reMin}, {$reMax}\n<b>Im min, max:</b> {$imMin}, {$imMax}",
                    "parse_mode" => "HTML"
                ]
            );
        } else {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Invalid format!\nUsage: <code>/cr02 min; max</code>\nWhere <code>min</code> and <code>max</code> are complex numbers.",
                    "parse_mode" => "HTML"
                ]
            );
        }
        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function cyf4(string $expr): bool
    {
        $expr = "plot [//math:".$expr."//]";
        $hash = md5($expr);
        $baseDir = BASEPATH."/storage/telegram/rmq";

        if (file_exists($baseDir."/{$hash}.gif")) {
            goto send_photo;
        }

        $oo = Exe::sendMessage(
            [
                "chat_id" => $this->data["chat_id"],
                "text" => "Calculating...",
                "reply_to_message_id" => $this->data["msg_id"]
            ]
        );

        is_dir($baseDir) or mkdir($baseDir);

        /**
         * Scrape 1
         */
        $url = "https://www.wolframalpha.com/widget/input/?input=".urlencode($expr)."&id=2e969d52de7679efab2533da1badafd2";
        $ch = curl_init($url);
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 120
            ]
        );
        $o = curl_exec($ch);
        if ($err = curl_error($ch)) {
            $ern = curl_errno($ch);
            curl_close($ch);
            $j = json_decode($oo["out"], true);
            Exe::editMessageText(
                [
                    "chat_id" => $this->data["chat_id"],
                    "message_id" => $j["result"]["message_id"],
                    "text" => "Error: ({$ern}) {$err}",
                ]
            );
            return true;
        }
        curl_close($ch);

        $e = explode("asynchronousPod('", $o, 2);
        if (count($e) < 2) goto invalid;
        $e = explode("'", $e[1], 2);
        if (count($e) < 2) goto invalid;
        $e = $e[0];

        /**
         * Scrape 2
         */
        $ch = curl_init($e);
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 120
            ]
        );
        $o = curl_exec($ch);
        if ($err = curl_error($ch)) {
            $ern = curl_errno($ch);
            curl_close($ch);
            $j = json_decode($oo["out"], true);
            Exe::editMessageText(
                [
                    "chat_id" => $this->data["chat_id"],
                    "message_id" => $j["result"]["message_id"],
                    "text" => "Error: ({$ern}) {$err}",
                ]
            );
            echo "zc ".$o["out"];
            return true;
        }
        curl_close($ch);

        $e = explode("src=\"", $o, 2);
        if (count($e) < 2) goto invalid;
        $e = explode("\"", $e[1], 2);
        if (count($e) < 2) goto invalid;
        $e = $e[0];

        /**
         * Image.
         */
        $ch = curl_init($e);
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 120
            ]
        );
        $o = curl_exec($ch);
        if ($err = curl_error($ch)) {
            $ern = curl_errno($ch);
            curl_close($ch);
            $j = json_decode($oo["out"], true);
            $o = Exe::editMessageText(
                [
                    "chat_id" => $this->data["chat_id"],
                    "message_id" => $j["result"]["message_id"],
                    "text" => "Error: ({$ern}) {$err}",
                ]
            );
            return true;
        }
        curl_close($ch);
        file_put_contents($baseDir."/{$hash}.gif", $o);

        send_photo:
        $o = Exe::sendPhoto(
            [
                "chat_id" => $this->data["chat_id"],
                "reply_to_message_id" => $this->data["msg_id"],
                "photo" => "https://telegram-bot.teainside.org/storage/rmq/{$hash}.gif",
                "caption" => "<pre>".htmlspecialchars($expr)."</pre>",
                "parse_mode" => "HTML"
            ]
        );
        return true;

        invalid:
        $j = json_decode($oo["out"], true);
        $o = Exe::editMessageText(
            [
                "chat_id" => $this->data["chat_id"],
                "message_id" => $j["result"]["message_id"],
                "text" => "Invalid data!",
            ]
        );
        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    private function exprCheck(string $expr): bool
    {
        if (file_exists("/tmp/telegram/calculus_lock/".md5($expr))) {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "text" => "Cannot retrieve the solution since another user is having the same problem in captcha.",
                    "reply_to_message_id" => $this->data["msg_id"]
                ]
            );
            return true;
        }
        return false;
    }
}
