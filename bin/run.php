<?php

require __DIR__."/../bootstrap/autoload.php";

loadConfig("telegram_bot");

$st = new \TeaBot\TeaBot($argv[1]);
$st->run();
