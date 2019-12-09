<?php

if (isset($checkAnswer)) {
    if (isset($extra, $answer)) {
        return $extra === ("=".(trim($answer)));
    }

    return false;
}

switch (rand(0, 0)) {
    case 0:
        $arr = ["x" , (rand(2, 10)."x") ,"\\sin(x)"];
        shuffle($arr);
        $p = ["-", "+"];
        $arr[1] = $p[rand(0, 1)].$arr[1];
        $arr[2] = $p[rand(0, 1)].$arr[2];
        $latex = "\\lim_{x \\to 0} \\frac{".implode("", $arr)."}{x}";
        // $latex = '\lim_{x \to 0} \frac{\sin(x)+x-2x}{x}';
        $photo = "https://api.teainside.org/latex_x.php?border=140&d=300&exp=".urlencode($latex);
        break;
    
    default:
        return false;
        break;
}

$ch = curl_init("https://api.teainside.org/teacalc2.php?key=8e7eaa2822cf3bf77a03d63d2fbdeb36df0a409f&expr=".urlencode($latex));
curl_setopt_array($ch,
    [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]
);
$o = curl_exec($ch);
curl_close($ch);
$o = json_decode($o, true);
$extra = $o["solutions"][0]["entire_result"];

$msg = "<b>Please solve this captcha problem to make sure you are a human or you will be kicked in 5 minutes. Reply your answer to this message!</b>\n\nEvaluate the following expression!";

$hash = md5($extra);
is_dir("/tmp/telegram/calculus_lock/") or mkdir("/tmp/telegram/calculus_lock/");
file_put_contents("/tmp/telegram/calculus_lock/".$hash, time());

return [
    "timeout" => 300,
    "extra" => $extra,
    "msg" => $msg,
    "photo" => $photo,
    "banned_hash" => $hash
];
