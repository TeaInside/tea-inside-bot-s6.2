<?php

if (!defined("__MY_AUTOLOAD")):
	define("__MY_AUTOLOAD", true);

	require __DIR__."/../config/global.php";
	require BASEPATH."/src/helpers/global.php";

	/**
	 * @param string $class
	 * @return void
	 */
	function myInternalAutoload(string $class): void
	{
		if (file_exists($f = BASEPATH."/src/classes/".str_replace("\\", "/", $class).".php")) {
			require $f;
		}
	}

	spl_autoload_register("myInternalAutoload");

endif;
