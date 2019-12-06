<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["BASEPATH"] = BASEPATH;
$const["STORAGE_PATH"] = STORAGE_PATH;


is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH, 0755);
if (!file_exists(STORAGE_PATH."/.gitignore")) {
    file_put_contents(STORAGE_PATH."/.gitignore", $ignoreAll);
}

$config = [
    "const" => &$const,
    "target_file" => __DIR__."/global.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
