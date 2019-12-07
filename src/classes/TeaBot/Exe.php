<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Exe
{

    /**
     * @param string $method
     * @param array  $parameters
     * @return array
     */
    public static function __callStatic(string $method, array $parameters = []): array
    {

        $param1 = array_shift($parameters);
        if (is_array($param1)) {
            $param1 = http_build_query($param1);
        }

        return self::execPost($method, $param1, ...$parameters);
    }

    /**
     * @param string $path
     * @param mixed  $body
     * @param string $queryString
     * @param array  $opt
     * @return array
     */
    public static function execPost(string $path, $body, ?string $queryString = null, $opt = []): array
    {
        $retried = false;
        $r = [];
        _start_curl:
        $ch = curl_init("https://api.telegram.org/bot".BOT_TOKEN."/".$path.(is_string($queryString) ? "?".$queryString : ""));
        $optf = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        ];
        foreach ($opt as $k => $v) {
            $optf[$k] = $v;
        }
        curl_setopt_array($ch, $optf);
        $r["out"] = curl_exec($ch);
        $r["info"] = curl_getinfo($ch);
        $r["error"] = curl_error($ch);
        $r["errno"] = curl_error($ch);
        curl_close($ch);
        echo $r["out"];

        if ($r["errno"]) {
            if (!$retried) {
                $retried = true;
                goto _start_curl;
            }
        }

        return $r;
    }

    /**
     * @param string $path
     * @param string $queryString
     * @param array  $opt
     * @return array
     */
    public static function execGet(string $path, ?string $queryString = null, $opt = []): array
    {
        $retried = false;
        $r = [];
        _start_curl:
        $ch = curl_init("https://api.telegram.org/bot".BOT_TOKEN."/".$path.(is_string($queryString) ? "?".$queryString : ""));
        $optf = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];
        foreach ($opt as $k => $v) {
            $optf[$k] = $v;
        }
        curl_setopt_array($ch, $optf);
        $r["out"] = curl_exec($ch);
        $r["info"] = curl_getinfo($ch);
        $r["error"] = curl_error($ch);
        $r["errno"] = curl_error($ch);
        curl_close($ch);

        if ($r["errno"]) {
            if (!$retried) {
                $retried = true;
                goto _start_curl;
            }
        }

        return $r;
    }

}
