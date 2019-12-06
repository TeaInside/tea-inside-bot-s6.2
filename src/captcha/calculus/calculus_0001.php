<?php

if (isset($checkAnswer)) {
    if (isset($extra, $answer)) {
        function factorial($number)
        { 
            $factorial = 1; 
            for ($i = 1; $i <= $number; $i++){ 
              $factorial = $factorial * $i; 
            } 
            return $factorial; 
        }
        return factorial((int)$extra) === (int)trim($answer);
    }

    return false;
}

$timeout = 300; // 5 minutes.
$extra = rand(0, 10);
$latex = "\\int_{0}^{\\infty} t^{".$extra."} e^{-t} dt";
$msg = "<b>Please solve this problem in 5 minutes to make sure you are a human or you will be kicked in 5 minutes. Reply your answer to this message!</b>\n\n".
    "Integrate the following expression:\n<code>".htmlspecialchars($latex, ENT_QUOTES, "UTF-8")."</code>";

$photo = "https://api.teainside.org/latex_x.php?d=300&exp=".urlencode($latex);

return [
    "timeout" => 300,
    "extra" => $extra,
    "msg" => $msg,
    "photo" => $photo,
];
