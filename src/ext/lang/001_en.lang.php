<?php

$lang = "en";
$targetFile = __DIR__."/{$lang}.h";
$langData = [
    "start.group" => "This command can only be used in private.",
    "start.private" => "Send /help to show the command list.",
    "help.group" => "This command can only be used in private.",

"help.private" => <<<HELP_PRIVATE
<b>Available Commands:</b>

<code>/c001 [math expression in latex]</code> Calculate math expression.

<code>/c002 [math expression in latex]</code> Calculate math expression with image output.

<code>/tr [from lang code] [to lang code] [text]</code> Translate text (google translate).

HELP_PRIVATE,

];
