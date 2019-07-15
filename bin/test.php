<?php

require __DIR__."/../src/build.php";

$json = '{
    "update_id": 345109045,
    "message": {
        "message_id": 67938,
        "from": {
            "id": 243692601,
            "is_bot": false,
            "first_name": "Ammar",
            "last_name": "Faizi",
            "username": "ammarfaizi2",
            "language_code": "en"
        },
        "chat": {
            "id": 243692601,
            "first_name": "Ammar",
            "last_name": "Faizi",
            "username": "ammarfaizi2",
            "type": "private"
        },
        "date": 1543988644,
        "text": "/debug",
        "entities": [
            {
                "offset": 0,
                "length": 6,
                "type": "bot_command"
            }
        ]
    }
}';

$libDir = __DIR__."/../storage/lib";

sh("/usr/bin/php7.3 -d extension='{$libDir}/teabot.so' ".__DIR__."/run.php ".escapeshellarg($json));
