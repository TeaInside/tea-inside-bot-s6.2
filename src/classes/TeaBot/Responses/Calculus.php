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
        $res = $this->execute($expr);
        if (isset($res["solutions"][0]["entire_result"])) {
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
            if ($res["solutions"][0]["entire_result"][0] === "=") {
                $reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
            } else {
                $reply = "(".$res["dym"]["originalEquation"].") \\;\\Rightarrow\\; (".$res["solutions"][0]["entire_result"].")";
            }
            $photo = "https://api.teainside.org/latex_x.php?d=600&exp=".urlencode($reply);
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
        if (preg_match('/^(\-?\d+)(?:\s*(\+|\-)\s*)(\-?\d+)(?:i\s*\;\s*)(\-?\d+)(?:\s*(\+|\-)\s*)(\-?\d+)i$/i', $expr, $m)) {
            $reMin = (int)$m[1];
            $imMin = (int)$m[3] * ($m[2] == "-" ? -1 : 1);
            $reMax = (int)$m[4];
            $imMax = (int)$m[6] * ($m[5] == "-" ? -1 : 1);
            $hash = md5($reMin.$imMin.$reMax.$imMax);

            $baseDir = BASEPATH."/storage/telegram/riemann_graph";

            if (file_exists($baseDir."/{$hash}.png")) {
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
            echo $url;
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
                Exe::editTextMessage(
                    [
                        "chat_id" => $this->data["chat_id"],
                        "message_id" => $j["result"]["message_id"],
                        "text" => "Error: ({$ern}) {$err}",
                    ]
                );
                return true;
            }
            curl_close($ch);
            file_put_contents($baseDir."/{$hash}.png", $o);

            send_photo:
            $o = Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => "https://telegram-bot.teainside.org/storage/riemann_graph/{$hash}.png",
                    "caption" => "<b>Re min, max:</b> {$reMin}, {$reMax}\n<b>Im min, max:</b> {$imMin}, {$imMax}",
                    "parse_mode" => "HTML"
                ]
            );
            echo "https://telegram-bot.teainside.org/storage/riemann_graph/{$hash}.png\n";
            echo $o["out"];
        } else {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Invalid format!\nUsage: <code>/cr02 a; b</code>\nWhere <code>a</code> and <code>b</code> are complex numbers.",
                    "parse_mode" => "HTML"
                ]
            );
        }
        return true;
    }
}
