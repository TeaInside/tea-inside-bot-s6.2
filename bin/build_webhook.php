<?php

if (file_exists(__DIR__."/webhook.php") && (filemtime(__DIR__."/webhook.php") > filemtime(__FILE__))) {
	exit(0);
}

$phpBinary = PHP_BINARY;
$runScript = __DIR__."/run.php";
$extensionLoad = "-d extension=".escapeshellarg(__DIR__."/../storage/lib/teabot.so");
$logFile = escapeshellarg(realpath(__DIR__."/..")."/logs/telegram/webhook.log");

$str = <<<STR
<?php
\$input = file_get_contents("php://input");
print shell_exec("sh -c ".escapeshellarg("{$phpBinary} {$extensionLoad} {$runScript} ".escapeshellarg(\$input)." >> {$logFile} 2>&1 &")." 2>&1");

STR;

file_put_contents(__DIR__."/webhook.php", $str);

print "Created ".__DIR__."/webhook.php\n";
