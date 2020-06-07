<?php

require __DIR__."/bootstrap/autoload.php";

loadConfig("telegram_bot");

$now = date("Y_m_d__H_i_s");
$caption = "srabatsrobot backup ".date("Y-m-d H:i:s");

shell_exec("cd /home/candragati; rm -vf v3.tar.gz; tar -c v3 | gzip -9 > v3.tar.gz; chown -R candragati:candragati v3.tar.gz; cp -vf v3.tar.gz /tmp/v3.tar.gz;");


if (file_exists("/tmp/v3.tar.gz")) {
  $ch = curl_init("https://api.telegram.org/bot".BOT_TOKEN."/sendDocument");
  $optf = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
      "caption" => $caption,
      "chat_id" => -1001226735471,
      "document" => new \CurlFile("/tmp/v3.tar.gz")
    ]
  ];
  curl_setopt_array($ch, $optf);
  echo curl_exec($ch);
  curl_close($ch);
  unlink("/tmp/v3.tar.gz");
}
