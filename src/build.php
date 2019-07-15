<?php


/**
 * @param string $cmd
 * @return void
 */
function sh(string $cmd): void
{
	$proc = proc_open($cmd, [], $pipes);
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
