<?php

function factorial($number)
{ 
    $factorial = 1; 
    for ($i = 1; $i <= $number; $i++){ 
      $factorial = $factorial * $i; 
    } 
    return $factorial; 
}

if (isset($checkAnswer)) {
    if (isset($extra, $answer)) {
        return (string)$extra === trim($answer);
    }

    return false;
}

$timeout = 300; // 5 minutes.

$extra = rand(0, 8);

$up1 = rand(10, 300);
$up2 = rand(10, 300);

$mul1 = rand(2, 10);
$mul2 = rand(2, 10);

$lw1 = $up1 - $mul1;
$lw2 = $up2 - $mul2;

$rd = ["x^{".$extra."}", "e^{-x}"];
shuffle($rd);

$latex = "\int_{".$lw1."}^{".$up1."} \int_{".$lw2."}^{".$up2."} \int_{0}^{\infty} (".implode(" ", $rd).") dx dy dz";

$extra = factorial($extra) * $mul1 * $mul2;

$hash = md5("=".$extra);

is_dir("/tmp/telegram/calculus_lock/") or mkdir("/tmp/telegram/calculus_lock/");
file_put_contents("/tmp/telegram/calculus_lock/".$hash, time());

$msg = "<b>Please solve this captcha problem to make sure you are a human or you will be kicked in 10 minutes. Reply your answer to this message!</b>\n\nIntegrate the following expression!";

$photo = "https://api.teainside.org/latex_x.php?border=200&d=300&exp=".urlencode($latex);

return [
    "timeout" => 600,
    "extra" => $extra,
    "msg" => $msg,
    "photo" => $photo,
    "banned_hash" => $hash
];
