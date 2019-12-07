<?php

if (preg_match('/^(\-?\d+)(?:\s*(\+|\-)\s*)(\-?\d+)(?:i\s*\;\s*)(\-?\d+)(?:\s*(\+|\-)\s*)(\-?\d+)i$/', $a, $m)) {
    $reMin = (int)$m[1];
    $imMin = (int)$m[3] * ($m[2] == "-" ? -1 : 1);
    $reMax = (int)$m[4];
    $imMax = (int)$m[6] * ($m[5] == "-" ? -1 : 1);;
}

var_dump($reMin, $reMax, $imMin, $imMax);