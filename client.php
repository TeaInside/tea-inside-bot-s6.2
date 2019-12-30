<?php

echo "Connecting...\n";

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, '127.0.0.1', 10001);

$json = json_encode(
	[
		"type" => "calculus",
		"sleep" => 10,
		"user_id" => "123123123",
		"chat_id" => "qweqweqwe",
		"join_msg_id" => "join",
		"captcha_msg_id" => "cc",
		"welcome_msg_id" => "ww",
		"banned_hash" => "bb",
		"mention" => "@@@@x"
	],
	JSON_UNESCAPED_SLASHES
);

socket_send($socket, sprintf("%07d", $len = strlen($json)), 7, 0);
socket_send($socket, $json, $len, 0);

socket_recv($socket, $buf, 100, 0);

echo $buf."\n";
socket_close($socket);
