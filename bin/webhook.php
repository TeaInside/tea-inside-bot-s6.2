<?php
$input = file_get_contents("php://input");
$logFile = __DIR__."/../logs/telegram/webhook.log";
print shell_exec("sh -c ".escapeshellarg("/usr/bin/php7.3 -d extension='/home/ammarfaizi2/project/now/bot-s6.2/storage/lib/teabot.so' /home/ammarfaizi2/project/now/bot-s6.2/bin/run.php ".escapeshellarg($input)." >> ".escapeshellarg($logFile)." 2>&1")." 2>&1");
