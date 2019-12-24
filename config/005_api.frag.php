<?php

require __DIR__."/../bootstrap/frag_autoload.php";

$const["SRABATSROBOT_API_KEY"] = SRABATSROBOT_API_KEY;

$config = [
    "const" => &$const,
    "target_file" => __DIR__."/api.php"
];

(new \ConfigBuilder\ConfigBuilder($config))->build();
