<?php

require __DIR__."/config.php";
require __DIR__."/src/build.php";

$extDir = __DIR__."/src/ext";
$buildDir = __DIR__."/build";
$releaseMode = false;
$configDir = __DIR__."/config";
$libDir = STORAGE_PATH."/lib";
$cwd = getcwd();
$forceBuild = array_search("-f", $argv) !== false;

if (isset($argv[1])) {
	switch ($argv[1]) {
		case "release":
			$releaseMode = true;
		break;
		
		case "clean":
			cleanBuiltData();
		break;
	}
}

build();

/**
 * @return void
 */
function build(bool $noExit = false): void
{
	global $extDir, $buildDir, $releaseMode, $forceBuild, $libDir, $cwd, $configDir;

	/**
	 * Build extension.
	 */
	$buildExtDir = $buildDir."/ext";
	if (!is_dir($buildDir)) {
		mmlog("Create build directory: %s", $buildDir);
		mkdir($buildDir, 0755);
	}
	if (!is_dir($buildExtDir)) {
		mmlog("Create ext build directory: %s", $buildExtDir);
		mkdir($buildExtDir, 0755);
	}
	// Scan ext directory.
	recursiveCallbackScanDir($extDir, function (string $dir, string $file) use ($extDir, $buildExtDir) {
		$baseD = explode($extDir, $dir, 2);
		if (isset($baseD[1])) {
			
			$targetDir = $buildExtDir."/".ltrim($baseD[1], "/");
			if (!is_dir($targetDir)) {
				mmlog("Create ext build directory: %s", $targetDir);
				mkdir($targetDir, 0755);
			}

			$sourceFile = $dir."/".$file;
			$targetFile = rtrim($targetDir, "/")."/".$file;

			if ((!file_exists($targetFile)) || (filemtime($sourceFile) > filemtime($targetFile))) {
				sh("cp -vf ".escapeshellarg($sourceFile)." ".escapeshellarg($targetFile));
			}
		}
	});

	chdir($buildExtDir);
	if (!file_exists("configure.lock")) {
		sh("phpize");
		sh("./configure");
		file_put_contents($buildExtDir."/configure.lock", time());
	}
	if ($forceBuild) {
		sh("make clean");
	}
	sh("make");
	sh("ln -svf ".escapeshellarg($buildExtDir."/modules/teabot.so")." ".escapeshellarg($libDir));

	chdir($cwd);

	/**
	 * Build config.
	 */
	recursiveCallbackScanDir($configDir, function (string $dir, string $file) {
		if (preg_match("/^.+\.php\.frag$/S", $file)) {
			sh(escapeshellarg(PHP_BINARY)." ".escapeshellarg($dir."/".$file));
		}
	});

	if (!$noExit) {
		exit(0);
	}
}

/**
 * @return void
 */
function cleanBuiltData(): void
{
	sh("rm -rfv build");
	exit(0);
}
