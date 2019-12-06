<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["QURAN_STORAGE_PATH"] = STORAGE_PATH."/quran";

if (!is_dir($const["QURAN_STORAGE_PATH"])) {
    @unlink($const["QURAN_STORAGE_PATH"]);
    mkdir($const["QURAN_STORAGE_PATH"], 0755);
}

if (!file_exists($const["QURAN_STORAGE_PATH"]."/.gitignore")) {
    file_put_contents($const["QURAN_STORAGE_PATH"]."/.gitignore", $ignoreAll);
}


$config = [
    "const" => &$const,
    "target_file" => __DIR__."/quran.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
