<?php

require __DIR__."/../../bootstrap/autoload.php";

loadConfig("telegram_bot");

if (!isset($argv[1])) {
	echo "Argument required!\n";
	exit(1);
}

TeaBot\API\Chart\WordsCloudID::generateWordCloud($argv[1]);
