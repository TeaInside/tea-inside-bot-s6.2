<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["QURAN_STORAGE_PATH"] = STORAGE_PATH."/quran";


is_dir($const["QURAN_STORAGE_PATH"]) or mkdir($const["QURAN_STORAGE_PATH"], 0755);

$config = [
	"const" => &$const,
	"target_file" => __DIR__."/quran.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
