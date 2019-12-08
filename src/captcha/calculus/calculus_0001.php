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
$extra = rand(0, 12);
$latex = "\\int_{0}^{\\infty} t^{".$extra."} e^{-t} dt";

$hash = md5("=".factorial($extra));
is_dir("/tmp/telegram/calculus_lock/") or mkdir("/tmp/telegram/calculus_lock/");
file_put_contents("/tmp/telegram/calculus_lock/".$hash, time());
$extra = factorial($extra);

$msg = "<b>Please solve this problem to make sure you are a human or you will be kicked in 5 minutes. Reply your answer to this message!</b>\n\n".
    "Integrate the following expression:\n<code>".htmlspecialchars($latex, ENT_QUOTES, "UTF-8")."</code>";

$photo = "https://api.teainside.org/latex_x.php?d=300&exp=".urlencode($latex);

return [
    "timeout" => 300,
    "extra" => $extra,
    "msg" => $msg,
    "photo" => $photo,
    "banned_hash" => $hash
];
