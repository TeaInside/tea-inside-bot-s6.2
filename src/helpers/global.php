<?php

/**
 * @param string $configName
 * @return bool
 */
function loadConfig(string $configName): void
{
	require BASEPATH."/config/".$configName.".php";
}
