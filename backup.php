<?php

require __DIR__."/bootstrap/autoload.php";

loadConfig("telegram_bot");

$now = date("Y_m_d__H_i_s");
$filename = "/tmp/srabatsrobot_v3_".$now.".tar.gz";

echo "Compressing data...\n";
shell_exec("cd /home/candragati; rm -vf v3.tar.gz; tar -c v3 | gzip -9 > v3.tar.gz; chown -R candragati:candragati v3.tar.gz; cp -vf v3.tar.gz ".escapeshellarg($filename));


if (file_exists($filename)) {

  $caption =
    "filename: ".basename($filename).
    "\ncreated_at: ".date("Y-m-d H:i:s").
    "\nmd5:".md5_file($filename).
    "\nsha1:".sha1_file($filename);


  $ch = curl_init("https://api.telegram.org/bot".BOT_TOKEN."/sendDocument");
  $optf = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
      "caption" => $caption,
      "chat_id" => -1001261147301,
      "document" => new \CurlFile($filename)
    ]
  ];
  curl_setopt_array($ch, $optf);
  echo curl_exec($ch);
  curl_close($ch);
  unlink($filename);
}
