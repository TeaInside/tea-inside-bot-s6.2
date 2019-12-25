<?php

$a = explode("\n", trim(file_get_contents(__DIR__."/extras/stopwords_id.txt")));
foreach ($a as $v) {
	$v = strtolower(trim($v));
	echo "\"{$v}\", ";
}

