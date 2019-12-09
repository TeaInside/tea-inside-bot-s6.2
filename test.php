<?php

$varList = ["x", "y", "z", "t", "u"];
$varList2 = ["\\alpha", "\\beta", "\\gamma", "\\theta"];

// Get first bound variable.
$varB1 = array_rand($varList2);
$tmp = $varList2[$varB1];
unset($varList2[$varB1]);
$varB1 = $tmp;

// Get second bound variable.
$varB2 = array_rand($varList2);
$tmp = $varList2[$varB2];
unset($varList2[$varB2]);
$varB2 = $tmp;

$mul1 = rand(1, 100) * 4;
$upB1 = rand(0, 1) ? rand(-300, -1) : rand(1, 300);
$lwB1 = $upB1 - $mul1;
$upB1 > 0 and $upB1 = "+".$upB1;
$lwB1 > 0 and $lwB1 = "+".$lwB1;
$upB1 == 0 and $upB1 = "";
$lwB1 == 0 and $lwB1 = "";

$mul2 = rand(1, 100);
$upB2 = rand(0, 1) ? rand(-300, -1) : rand(1, 300);
$lwB2 = $upB2 - $mul2;
$upB2 > 0 and $upB2 = "+".$upB2;
$lwB2 > 0 and $lwB2 = "+".$lwB2;
$upB2 == 0 and $upB2 = "";
$lwB2 == 0 and $lwB2 = "";


$inExpr = [
    "\\sin(x)",
    ["\\frac{x^{3}}{\pi^{4}}", "\\pi^{-4} x^{3}"][rand(0, 1)]
];
shuffle($inExpr);

$latex = "\\int_{".$varB2.$lwB2."}^{".$varB2.$upB2."} \\int_{".$varB1.$lwB1."}^{".$varB1.$upB1."} \\int_{\\pi}^{2\\pi} (".implode("+", $inExpr).") dx dy dz";

echo $latex."\n";

echo "mul1 = $mul1\n";
echo "mul2 = $mul2\n";
$extra = $mul1 * $mul2 * (7/4);
echo $extra."\n";