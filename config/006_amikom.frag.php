<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["AMIKOM_SECRET_KEY"] = AMIKOM_SECRET_KEY;
$const["AMIKOM_X_API_KEY"] = AMIKOM_X_API_KEY;


is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH, 0755);
if (!file_exists(STORAGE_PATH."/.gitignore")) {
    file_put_contents(STORAGE_PATH."/.gitignore", $ignoreAll);
}

$config = [
    "const" => &$const,
    "target_file" => __DIR__."/amikom.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
