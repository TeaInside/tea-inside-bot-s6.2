<?php

require __DIR__."/bootstrap/autoload.php";

loadConfig("telegram_bot");

TeaBot\Exe::sendMessage(
  [
    "text" => "backup_test",
    "chat_id" => -1001226735471
  ]
);