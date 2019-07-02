<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["BASEPATH"] = realpath(__DIR__."/..");
$const["STORAGE_PATH"] = $const["BASEPATH"]."/storage";

$config = [
	"const" => &$const,
	"target_file" => __DIR__."/global.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
