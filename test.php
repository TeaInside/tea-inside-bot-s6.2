<?php

require __DIR__."/bootstrap/autoload.php";

loadConfig("telegram_bot");

$st = new TeaBot\CaptchaThread(BOT_TOKEN, "/tmp/telegram/captcha_handler");

$type = "calculus";
$userId = 123;
$chatId = "-1001128970273";
$joinMsgId = 100;
$captchaMsgId = 300;
$welcomeMsgId = 400;
$bannedHash = "qweasd";
$mention = "@";

for ($i=0; $i < 1; $i++) { 
	$a = $st->dispatch(
		$type,
		0,
		$userId,
		$chatId,
		$joinMsgId,
		$captchaMsgId,
		$welcomeMsgId,
		$bannedHash,
		$mention
	);
	var_dump($a);
}

echo "\ndone!";
sleep(100);
