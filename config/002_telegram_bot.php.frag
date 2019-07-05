<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["TELEGRAM_STORAGE_PATH"] = TELEGRAM_STORAGE_PATH;

$const["BOT_TOKEN"] = TELEGRAM_BOT_TOKEN;
$const["PDO_PARAM"] = [
	"mysql:host=".TELEGRAM_BOT_DB_HOST.";port=".TELEGRAM_BOT_DB_PORT.";dbname=".TELEGRAM_BOT_DB_NAME,
	TELEGRAM_BOT_DB_USER,
	TELEGRAM_BOT_DB_PASS,
	[
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	]
];
$const["SUDOERS"] = TELEGRAM_BOT_SUDOERS;

$config = [
	"const" => &$const,
	"target_file" => __DIR__."/telegram_bot.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
