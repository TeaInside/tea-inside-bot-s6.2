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


is_dir(TELEGRAM_STORAGE_PATH) or mkdir(TELEGRAM_STORAGE_PATH, 0755);
if (!file_exists(TELEGRAM_STORAGE_PATH."/.gitignore")) {
    file_put_contents(TELEGRAM_STORAGE_PATH."/.gitignore", $ignoreAll);
}

$config = [
    "const" => &$const,
    "target_file" => __DIR__."/telegram_bot.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
