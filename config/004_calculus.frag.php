<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["CALCULUS_STORAGE_PATH"] = STORAGE_PATH."/calculus";
$const["CALCULUS_API_KEY"] = CALCULUS_API_KEY;
if (!is_dir($const["CALCULUS_STORAGE_PATH"])) {
    @unlink($const["CALCULUS_STORAGE_PATH"]);
    mkdir($const["CALCULUS_STORAGE_PATH"], 0755);
}

if (!is_dir($const["CALCULUS_STORAGE_PATH"]."/cache")) {
    @unlink($const["CALCULUS_STORAGE_PATH"]."/cache");
    mkdir($const["CALCULUS_STORAGE_PATH"]."/cache");
}

if (!file_exists($const["CALCULUS_STORAGE_PATH"]."/.gitignore")) {
    file_put_contents($const["CALCULUS_STORAGE_PATH"]."/.gitignore", $ignoreAll);
}

$config = [
    "const" => &$const,
    "target_file" => __DIR__."/calculus.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
