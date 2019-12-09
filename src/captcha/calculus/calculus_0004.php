<?php

if (isset($checkAnswer)) {
    if (isset($extra, $answer)) {
        return (string)$extra === trim($answer);
    }

    return false;
}

$timeout = 300; // 5 minutes.

switch (rand(1, 1)) {
    case 1:
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
        $extra = $mul1 * $mul2 * (7/4);
        break;
    
    default:
        break;
}

$hash = md5("=".$extra);

is_dir("/tmp/telegram/calculus_lock/") or mkdir("/tmp/telegram/calculus_lock/");
file_put_contents("/tmp/telegram/calculus_lock/".$hash, time());

$msg = "<b>Please solve this problem to make sure you are a human or you will be kicked in 5 minutes. Reply your answer to this message!</b>\n\n".
    "Integrate the following expression:\n<code>".htmlspecialchars($latex, ENT_QUOTES, "UTF-8")."</code>";

$photo = "https://api.teainside.org/latex_x.php?d=400&exp=".urlencode($latex);

return [
    "timeout" => 300,
    "extra" => $extra,
    "msg" => $msg,
    "photo" => $photo,
    "banned_hash" => $hash
];
