<?php

if (file_exists(__DIR__."/longpoll.php") && (filemtime(__DIR__."/longpoll.php") > filemtime(__FILE__))) {
    exit(0);
}

$phpBinary = PHP_BINARY;
$runScript = __DIR__."/run.php";
$extensionLoad = "-d extension=".escapeshellarg(__DIR__."/../storage/lib/teabot.so");
$logFile = escapeshellarg(realpath(__DIR__."/..")."/logs/telegram/longpoll.log");

$str = <<<STR
<?php

require __DIR__."/../bootstrap/autoload.php";

loadConfig("telegram_bot");
use TeaBot\Exe;

\$bot = Exe::getMe();
\$bot = json_decode(\$bot['out'], True);

\$line = PHP_EOL.str_repeat("-", 20).PHP_EOL;
print "\$line LongPoll Method \$line";
print "Nama\t: ".\$bot['result']['first_name'].PHP_EOL;
print "Id\t: ".\$bot['result']['id'].PHP_EOL;
print "Uname\t: @".\$bot['result']['username'].PHP_EOL;

\$last_id = null;
while (true) {
    \$params = [];
    if (!empty(\$last_id)) {
        \$params = ['offset' => \$last_id + 1, 'limit' => 1];
    }
    \$updates = Exe::getUpdates(\$params);
    \$updates = json_decode(\$updates['out'], True);
    if (!empty(\$updates['result'])) {
        foreach(\$updates['result'] as \$sumber){
            \$output = "\$line %s \$sumber[update_id] \$line%s";
            \$last_id = \$sumber['update_id'];
            if (isset(\$sumber['message']['date']) && \$sumber['message']['date'] < (time() - 120)) {
                print sprintf(\$output, '-- Pass --');
            }else{
                print sprintf(\$output, 'Query ID :', json_encode(\$sumber));
                \$sumber = json_encode(\$sumber);
                shell_exec("sh -c ".escapeshellarg("{$phpBinary} {$extensionLoad} {$runScript} ".escapeshellarg(\$sumber)." >> {$logFile} 2>&1 &")." 2>&1");
            }
        }
    }
    sleep(1);
}
STR;

file_put_contents(__DIR__."/longpoll.php", $str);

print "Created ".__DIR__."/longpoll.php\n";
