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
  return sendFile($filename);
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
  return sendFile($filename);
};


$jobs["srabatsrobot_v3"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/srabatsrobot_v3_".$now.".tar.gz";
  shell_exec("cd /home/candragati; rm -vf v3.tar.gz; tar -c v3 | gzip -9 > v3.tar.gz; chown -R candragati:candragati v3.tar.gz; cp -vf v3.tar.gz ".escapeshellarg($filename)." 2>&1");
  shell_exec("kill -9 \$(ps aux | grep python | grep timerbot | awk '{print \$2}')");
  shell_exec("su - candragati -c \"nohup sh -c 'cd /home/candragati/v3; python3 timerbot31.py >> /dev/null 2>&1 &' >> /dev/null 2>&1 &\";");
  return sendFile($filename);
};


$jobs["thefox"] = function () {
  $now = date("Y_m_d__H_i_s");
  $filename = "/tmp/thefox_".$now.".tar.gz";
  shell_exec("cd /home/; tar -c thefox | gzip -9 > ".escapeshellarg($filename));
  return sendFile($filename);
};


$aStart = microtime(true);
$r = "<b>[Time taken for each job]</b>\n";
$r2 = "<b>[File size for each job]</b>\n";
$totalSize = 0;
foreach ($jobs as $k => $callback) {
  $start = microtime(true);
  $filesize = $callback();
  $k = htmlspecialchars($k, ENT_QUOTES, "UTF-8");
  $r .= "<b>{$k}</b> = <code>".round(microtime(true) - $start, 6)."</code> s\n";
  $r2 .= "<b>{$k}</b> = <code>".$filesize."</code> bytes\n";
  $totalSize += $filesize;
}
$r .= "\n<b>Total time taken</b> = <code>".round(microtime(true) - $aStart, 6)."</code> s";
$r2 .= "\n<b>Total file size</b> = <code>".$totalSize."</code> bytes";

TeaBot\Exe::sendMessage(
  [
    "chat_id" => -1001261147301,
    "text" => $r,
    "parse_mode" => "HTML"
  ]
);

TeaBot\Exe::sendMessage(
  [
    "chat_id" => -1001261147301,
    "text" => $r2,
    "parse_mode" => "HTML"
  ]
);

function sendFile($filename)
{
  $filesize = filesize($filename);

  $caption =
    "file name: ".basename($filename).
    "\nfile size: ".$filesize." bytes".
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

  return $filesize;
}
