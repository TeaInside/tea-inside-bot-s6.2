<?php

namespace TeaBot\Plugins\Paxel;

/**
 * @package \Paxel
 */
class Paxel
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $userFile;

    /**
     * @var array
     */
    public $userData;

    /**
     * @param string $username
     * @param string $password
     *
     * Constructor.
     */
    public function __construct(string $username, string $password)
    {
        if (!defined("PAXEL_DIR")) {
            echo "PAXEL_DIR is not defined!\n";
            exit;
        }

        $this->username = $username;
        $this->password = $password;
        $this->userFile = PAXEL_DIR."/".sha1($username.$password).".json";
        $this->userData = file_exists($this->userData) ?
            json_decode(file_get_contents($this->userData), true) :
            [];
    }

    public function login()
    {
        $o = $this->qurl("https://api.paxel.co/apg/api/v1/login",
            [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "username" => $this->username,
                    "password" => $this->password
                ])
            ]
        );
        $json = json_decode($o["out"], true);

        file_put_contents(
            $this->userFile, json_encode($json,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return isset($json["data"]["api_token"]);
    }

    /**
     * @param string $url
     * @param array  $opt
     * @return array
     */
    private function qurl(string $url, array $opt = []): array
    {
        $ch = curl_init($url);
        $optf = [
            CURLOPT_USERAGENT => "okhttp/3.12.1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "accept: application/json, text/plain, */*",
                "x-player: 89edf7a9-693a-4fcc-901c-7f9085b5990b",
                "Content-Type: application/json",
                "Connection: Keep-Alive",
                "Accept-Encoding: gzip"
            ]
        ];

        foreach ($opt as $k => $v) {
            $optf[$k] = $v;
        }

        curl_setopt_array($ch, $optf);
        $out = curl_exec($ch);
        $err = curl_error($ch);
        $ern = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($err) {
            self::log(1, "Curl error occured: %s\n", $err);
        } else {
            $out = gzdecode($out);
        }

        return [
            "out" => $out,
            "err" => $err,
            "ern" => $ern,
            "info" => $info
        ];
    }

    /**
     * @param int    $logLevel
     * @param string $format
     * @param mixed  ...$param
     * @return void
     */
    public static function log(int $logLevel, string $format, ...$param)
    {
        global $globalLogLevel;
        if (!isset($globalLogLevel)) {
            $globalLogLevel = 3;
        }

        if ($logLevel <= $globalLogLevel) {
            printf("[%s] %s\n", date("Y-m-d H:i:s"), vsprintf($format, $param));
        }
    }
}
