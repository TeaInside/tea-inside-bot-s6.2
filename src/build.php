<?php


/**
 * @param string $cmd
 * @return void
 */
function sh(string $cmd): void
{
	$fd = [
		STDIN,
		["pipe", "w"],
		["pipe", "w"]
	];
	$internalCmd = "sh -c ".escapeshellarg($cmd)."; echo exit_code__:$?;";
	$proc = proc_open($internalCmd, $fd, $pipes);

	while ($r = fread($pipes[1], 1024)) {
		$b = $r;
		if (!strncmp($b, "exit_code__:", 12)) {
			break;
		}
		print $r;
	}
	$b = explode("exit_code__:", trim($b), 2);
	if (isset($b[1]) && ($b[1] !== "0")) {
		print "\n\nAn error occured:\n";
		print "Command ".escapeshellarg($cmd)." returned non zero exit code!\n";
		print "Returned exit code: {$b[1]}\n\n";
		exit(1);
	}

	proc_close($proc);
}

/**
 * @param string $format
 * @param mixed  ...$args
 * @return void
 */
function mmlog(string $format, ...$args): void
{
	printf("[%s]: %s\n", date("Y-m-d H:i:s"), sprintf($format, ...$args));
}

/**
 * @param string	$directory
 * @param ?callable	$callback
 * @return void
 */
function recursiveCallbackScanDir(string $directory, callable $callback = null): void
{
	$scan = scandir($directory);
	unset($scan[0], $scan[1]);
	foreach ($scan as $file) {
		$absFile = $directory."/".$file;
		if (is_dir($absFile)) {
			recursiveCallbackScanDir($absFile, $callback);
		} else {
			$callback($directory, $file);
		}
	}
}
