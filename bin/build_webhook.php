<?php

$phpBinary = PHP_BINARY;
$runScript = __DIR__."/run.php";
$extensionLoad = "-d extension=".escapeshellarg(realpath(__DIR__."/../storage/lib/")."/teabot.so");

$str = <<<STR
<?php
\$input = file_get_contents("php://input");
\$logFile = __DIR__."/../logs/telegram/webhook.log";
print shell_exec("sh -c ".escapeshellarg("{$phpBinary} {$extensionLoad} {$runScript} ".escapeshellarg(\$input)." >> ".escapeshellarg(\$logFile)." 2>&1")." 2>&1");

STR;

file_put_contents(__DIR__."/webhook.php", $str);
