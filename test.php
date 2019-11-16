<?php

$o = curl(
	"https://www.symbolab.com/solver/limit-calculator/%5Clim_%7Bx%5Cto%5Cinfty%7D%5Cleft(x%5E%7B2%7D%5Cright)",
	[
		CURLOPT_CUSTOMREQUEST => "HEAD",
		CURLOPT_HEADER => true,
		CURLOPT_WRITEFUNCTION => function ($ch, $str) {
			var_dump($str);
			return strlen($str);
		}
	]
);

var_dump($o["out"]);

/**
 * @param string $url
 * @param array  $opt
 * @return array
 */
function curl(string $url, array $opt = []): array
{
	$ch = curl_init($url);
	$optf = [
		// CURLOPT_HTTPHEADER => DEFAULT_CALCULUS_HEADERS,
		CURLOPT_VERBOSE => true,
		CURLOPT_HTTP_VERSION => 2,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false
	];
	foreach ($opt as $k => $v) {
		$optf[$k] = $v;
	}
	curl_setopt_array($ch, $optf);
	$o = curl_exec($ch);
	$err = curl_error($ch);
	$ern = curl_errno($ch);
	return [
		"out" => $o,
		"err" => $err,
		"ern" => $ern
	];
}