<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["BASEPATH"] = BASEPATH;
$const["STORAGE_PATH"] = STORAGE_PATH;

$config = [
	"const" => &$const,
	"target_file" => __DIR__."/global.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
