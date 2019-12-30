<?php

require __DIR__."/bootstrap/autoload.php";

loadConfig("telegram_bot");

function captchaThreadInternal(string $method, string $payload)
{
	echo $method." ".$payload.PHP_EOL;
}

$exe = function (string $method, string $payload) {
	echo $method." ".$payload.PHP_EOL;
	// echo "qwe";
	// TeaBot\Exe::{$method}($payload);
};

$st = new TeaBot\CaptchaThread(
	BOT_TOKEN,
	"/tmp/telegram/captcha_handler",
	$exe
);

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
sleep(100);

// $b = $st->dispatch(0, function () {
// 	while (true) {
// 		echo "*";
// 		usleep(10000);
// 	}
// });

// echo $a." ".$b.PHP_EOL;


// $st->dispatch(0, function () {
// 	while (true) {
// 		echo "x";
// 		usleep(10000);
// 	}
// });
