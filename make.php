<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 */

if (!isset($argv[1])) {
	goto build;
}

switch ($argv[1]) {
	case "release":
	case "build":
		goto build;
	break;
	case "clean":
		goto clean;
	break;
	default:
		print "Invalid command '{$argv[1]}'\n";
		exit(1);
	break;
}

build:
	$releaseMode = (isset($argv[1]) && ($argv[1] === "release"));
	$dir = __DIR__."/config";
	$scan = scandir($dir);
	unset($scan[0], $scan[1]);
	foreach ($scan as $file) {
		if (preg_match("/^.+\.php\.frag$/Si", $file)) {
			$cmd = escapeshellarg(PHP_BINARY)." ".escapeshellarg($dir."/".$file);
			print "Executing: {$cmd}...";
			$sh = shell_exec($cmd." 2>&1; echo exit_code:$?");
			if (preg_match("/exit_code:(\d+)/", $sh, $m)) {
				if ($m[1] !== "0") {
					print "\n\nExit code for file ".$dir."/".$file." is not zero\n\n";
					print "Output: ".$sh;
					exit(1);
				}
			} else {
				print "\n\nCouldn't get exit code for file ".$dir."/".$file."\n";
				exit(1);
			}
			print " OK\n";
		}
	}
	exit(0);

clean:
	$dir = __DIR__."/config";
	$scan = scandir($dir);
	unset($scan[0], $scan[1]);
	foreach ($scan as $file) {
		if (preg_match("/^.+\.php\.frag$/Si", $file)) {
			$cmd = escapeshellarg(PHP_BINARY)." ".escapeshellarg($dir."/".$file)." clean";
			print "Executing: {$cmd}...";
			$sh = shell_exec($cmd." 2>&1; echo exit_code:$?");
			if (preg_match("/exit_code:(\d+)/", $sh, $m)) {
				if ($m[1] !== "0") {
					print "\n\nExit code for file ".$dir."/".$file." is not zero\n\n";
					print "Output: ".$sh;
					exit(1);
				}
			} else {
				print "\n\nCouldn't get exit code for file ".$dir."/".$file."\n";
				exit(1);
			}
			print " OK\n";
		}
	}
	exit(0);
