<?php

$a = "1+3i ; 0.5 + 5i";
if (preg_match('/^(\-?[\d\.]+)(?:\s*(\+|\-)\s*)(\-?[\d\.]*)(?:i\s*\;\s*)(\-?[\d\.]+)(?:\s*(\+|\-)\s*)(\-?[\d\.]*)i$/', $a, $m)) {
    $reMin = (float)$m[1];
    $imMin = ($m[3] === "" ? 1 : (float)$m[3]) * ($m[2] == "-" ? -1 : 1);
    $reMax = (float)$m[4];
    $imMax = ($m[6] === "" ? 1 : (float)$m[6]) * ($m[5] == "-" ? -1 : 1);
}

var_dump($reMin, $reMax, $imMin, $imMax);