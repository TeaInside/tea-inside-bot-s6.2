<?php

require __DIR__."/../src/build.php";

$json = '{
    "update_id": 345237741,
    "message": {
        "message_id": 22283,
        "from": {
            "id": 243692601,
            "is_bot": false,
            "first_name": "Ammar",
            "last_name": "Faizi",
            "username": "ammarfaizi2",
            "language_code": "en"
        },
        "chat": {
            "id": -1001128970273,
            "title": "Private Cloud",
            "type": "supergroup"
        },
        "date": 1555600051,
        "text": "/quran 1:1",
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

sh(
	"/usr/bin/php7.3 -d extension='{$libDir}/teabot.so' ".__DIR__."/run.php ".escapeshellarg($json)
);
