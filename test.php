<?php

$st = new TeaBot\CaptchaThread;
$st->run();


$a = $st->dispatch(function () {
	while (true) {
		echo "aaa\n";
		sleep(1);
	}
});
$b = $st->dispatch(function () {
	while (true) {
		echo "bbb\n";
		sleep(1);
	}
});

echo $a." ".$b."\n";

while (true) {
	echo "ccc\n";
	sleep(1);
}
