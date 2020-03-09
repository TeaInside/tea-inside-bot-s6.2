<?php

$ch = curl_init("https://www.worldometers.info/coronavirus/");
curl_setopt_array($ch,
[
CURLOPT_RETURNTRANSFER => true,
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => false
]
);
$o = curl_exec($ch);

$sdt = $fst = $cmt = 0;

$r = "[Coronavirus vt (china only)]\ndatetime: ".gmdate("Y-m-d H:i:s")." (GMT +0 qmq)\n";

if (preg_match(
'/Indonesia <\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>[^\>\<]+?<\/td>\s<td[^\>]+?>([^\>\<]+?)</Ui',
$o, $m
)) {
$m[3] = trim($m[3]);
$m[2] = trim($m[2]);
$m[1] = trim($m[1]);
$sdt = (int)str_replace(",", "", trim($m[3]));
$r .= "sdt: ".$m[3]."\n";
$fst = (int)str_replace(",", "", trim($m[2]));
$r .= "fst: ".$m[2]."\n";
$cmt = (int)str_replace(",", "", trim($m[1]));
$r .= "cmt: ".$m[1]."\n";
}

var_dump($m);

$r .= "percent fst: ".number_format($fst/$cmt * 100, 15)." %\n";
$r .= "mean_total: ".number_format(($sdt+$fst+$cmt)/3, 15)."\n";
$r .= "pt: ".number_format(($sdt*$fst*$cmt), 15)."\n";

echo $r;