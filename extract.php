<?php

$a = file_get_contents("test.html");

if (preg_match_all("/<a href=\".+?sl=(.+?)&/", $a, $m)) {
    print "[";
    foreach($m[1] as $s) {
        print "\"{$s}\", ";
    }
    print "]";
}