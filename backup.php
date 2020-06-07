<?php

error_reporting(0);

require __DIR__."/bootstrap/autoload.php";
require __DIR__."/config.php";

loadConfig("telegram_bot");


$jobs = [];

$jobs["teabot"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/teabot_".$now.".sql.gz";
  echo shell_exec(
    "mysqldump ".
    " -h ".escapeshellarg(TELEGRAM_BOT_DB_HOST).
    " -P ".escapeshellarg(TELEGRAM_BOT_DB_PORT).
    " -u ".escapeshellarg(TELEGRAM_BOT_DB_USER).
    " -p".escapeshellarg(TELEGRAM_BOT_DB_PASS).
    " --default-character-set=utf8mb4 --hex-blob teabot | gzip -9 > ".
    escapeshellarg($filename)." 2>&1"
  );
  sendFile($filename);
};


$jobs["rsudciamis"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/rsudciamis_antrian_".$now.".sql.gz";
  echo shell_exec(
    "mysqldump ".
    " -h ".escapeshellarg(TELEGRAM_BOT_DB_HOST).
    " -P ".escapeshellarg(TELEGRAM_BOT_DB_PORT).
    " -u ".escapeshellarg(TELEGRAM_BOT_DB_USER).
    " -p".escapeshellarg(TELEGRAM_BOT_DB_PASS).
    " --default-character-set=utf8mb4 --hex-blob rsudciamis_antrian | gzip -9 > ".
    escapeshellarg($filename)." 2>&1"
  );
  sendFile($filename);
};


$jobs["srabatsrobot_v3"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/srabatsrobot_v3_".$now.".tar.gz";
  shell_exec("cd /home/candragati; rm -vf v3.tar.gz; tar -c v3 | gzip -9 > v3.tar.gz; chown -R candragati:candragati v3.tar.gz; cp -vf v3.tar.gz ".escapeshellarg($filename)." 2>&1");
  sendFile($filename);
};


$jobs["thefox"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/thefox_".$now.".tar.gz";
  shell_exec("cd /home/; tar -c thefox | gzip -9 > ".escapeshellarg($filename));
  sendFile($filename);
};


$aStart = microtime(true);
$r = "Time taken for each job:\n";
foreach ($jobs as $k => $callback) {
  $start = microtime(true);
  $callback();
  $k = htmlspecialchars($k, ENT_QUOTES, "UTF-8");
  $r .= "<b>{$k}</b> = <code>".round(microtime(true) - $start, 6)."</code> s\n";
}
$r .= "\nTotal time taken: <code>".round(microtime(true) - $aStart, 6)."</code>";

var_dump(\TeaBot\Exe::sendMessage(
  [
    "chat_id" => -1001261147301,
    "text" => $r,
    "parse_mode" => "HTML"
  ]
)["out"]);


function sendFile($filename)
{
  $caption =
    "filename: ".basename($filename).
    "\ncreated_at: ".date("Y-m-d H:i:s").
    "\nmd5: ".md5_file($filename).
    "\nsha1: ".sha1_file($filename);

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
