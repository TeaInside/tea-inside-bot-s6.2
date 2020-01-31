<?php

namespace TeaBot\Responses;

use stdClass;
use Exception;
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
     * @var bool
     */
    private $abuse = false;

    /**
     * @var array
     */
    private const ALLOWED_GROUPS = [
        -1001120283944, // @TeaInside
        -1001128531173, // Tea Inside
        -1001162202776, // Koding Teh
        -1001286444191, // Dark Tea Inside
        -1001362276542, // PHP LTM
        -1001128970273, // Private Cloud
        -1001377289579, // /\
    ];

    /**
     * @param \TeaBot\Data &$data
     *
     * Constructor.
     */
    public function __construct(Data &$data)
    {
        parent::__construct($data);
        loadConfig("calculus");

        if (($data["chat_type"] === "group") &&
            (!in_array($data["chat_id"], self::ALLOWED_GROUPS))) {
            $this->abuse = true;
        }
    }

    /**
     * @return bool
     */
    private function abuseCheck(): bool
    {
        if ($this->abuse) {
            $o = json_decode(Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Due to excessive abuse, this feature can only be used in private message and part of internal TeaInside groups.\n\nIf you are an administrator of this group, you can ask to enable this feature for this group. Contact @TeaInside for details, thanks!"
                ]
            )["out"], true);
            sleep(5);
            Exe::deleteMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "message_id" => $this->data["msg_id"]
                ]
            );
            Exe::deleteMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "message_id" => $o["result"]["message_id"]
                ]
            );
            return true;
        }
        return false;
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
    public function lxt1(string $expr): bool
    {
        if ($this->abuseCheck()) return true;

        $rr = self::latexGen(
            [
                "content" => $expr,
                "d" => 600,
                "border" => "200"
            ]
        );

        $error = true;
        if (isset($rr["out"]["status"]) && ($rr["out"]["status"] === "success")) {
            $o = Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => $photo,
                    "caption" => "<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>",
                    "parse_mode" => "html"
                ]
            );
            $o = json_decode($o["out"], true);
            $error = !$o["ok"];
        }

        if ($error) {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Syntax error!",
                    "parse_mode" => "html"
                ]
            );
        }
        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function lxt0(string $expr): bool
    {
        if ($this->abuseCheck()) return true;

        $rr = self::latexGen(
            [
                "content" => $expr,
                "d" => 600
            ]
        );

        $error = true;
        if (isset($rr["out"]["status"]) && ($rr["out"]["status"] === "success")) {
            $o = Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => "https://latex.teainside.org/latex/png/".$rr["out"]["res"].".png",
                    "caption" => "<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>",
                    "parse_mode" => "html"
                ]
            );

            $o = json_decode($o["out"], true);
            $error = !$o["ok"];
        }

        if ($error) {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => "Syntax error!",
                    "parse_mode" => "html"
                ]
            );
        }
        return true;
    }

    /**
     * @param string $expr
     * @return bool
     */
    public function c001(string $expr): bool
    {
        if ($this->abuseCheck()) return true;

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
        if ($this->abuseCheck()) return true;

        $res = $this->execute($expr);

        $photo = null;
        if (isset($res["solutions"][0]["entire_result"])) {

            if ($this->exprCheck($res["solutions"][0]["entire_result"])) return true;

            if ($res["solutions"][0]["entire_result"][0] === "=") {
                $reply = $res["dym"]["originalEquation"].$res["solutions"][0]["entire_result"];
            } else {
                $reply = "\\left(".$res["dym"]["originalEquation"]."\\right) \\;\\Rightarrow\\; \\left(".$res["solutions"][0]["entire_result"]."\\right)";
            }
            $reply = str_replace(
                [
                    "\xe2\x88\x82",
                    "\xce\xb5"
                ],
                [
                    "{\\partial}",
                    "{\\epsilon}"
                ],
                $reply
            );

           $rr = self::latexGen(
                [
                    "content" => $reply,
                    "d" => 600,
                    "border" => "200"
                ]
            );

            if (isset($rr["out"]["status"]) && ($rr["out"]["status"] === "success")) {
                $photo = "https://latex.teainside.org/latex/png/".$rr["out"]["res"].".png";
            } else {
                $reply = "Cannot render PNG image due internal error. Please report to @TeaInside.\n\nLaTex result:\n<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>";
            }
        } else {
            $reply = isset($res["errorMessage"]) ? $res["errorMessage"] : "Couldn't get the result";
        }

        if (isset($photo)) {
            $o = Exe::sendPhoto(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "photo" => $photo,
                    "caption" => "<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>",
                    "parse_mode" => "html"
                ]
            );
            $o = json_decode($o["out"], true);
            if (!$o["ok"]) {
                 Exe::sendMessage(
                    [
                        "chat_id" => $this->data["chat_id"],
                        "reply_to_message_id" => $this->data["msg_id"],
                        "text" => "Cannot render PNG image due internal error. Please report to @TeaInside.\n\nLaTex result:\n<pre>".htmlspecialchars($reply, ENT_QUOTES, "UTF-8")."</pre>",
                        "parse_mode" => "html"
                    ]
                );
            }
        } else {
            Exe::sendMessage(
                [
                    "chat_id" => $this->data["chat_id"],
                    "reply_to_message_id" => $this->data["msg_id"],
                    "text" => $reply,
                    "parse_mode" => "html"
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
        if ($this->abuseCheck()) return true;

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
        if ($this->abuseCheck()) return true;

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

    /**
     * @param array $params
     * @return array
     */
    public static function latexGen(array $params): array
    {
        if (!isset($params["content"])) {
            throw new Exception("latexGen's parameter doesn't contain \"content\" parameter!");
        }

        $ch = curl_init("https://latex.teainside.org/api.php?action=tex2png");
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_SLASHES)
            ]
        );
        $o = curl_exec($ch);
        $ern = curl_errno($ch);
        if ($err = curl_error($ch)) {
            $ret = [
                "error" => "({$ern}): {$err}",
                "out" => null
            ];
        } else {
            $ret = [
                "error" => null,
                "out" => json_decode($o, true)
            ];
        }
        $ret["info"] = curl_getinfo($ch);

        curl_close($ch);

        var_dump($ret);
        return $ret;
    }
}
