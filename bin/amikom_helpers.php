<?php

$amikomSecretKey = $amikomXApiKey = "";

function initAmikomSecretKey()
{
    global $amikomSecretKey, $amikomXApiKey;
    loadConfig("amikom");
    $amikomXApiKey = AMIKOM_X_API_KEY;
    $amikomSecretKey = AMIKOM_SECRET_KEY;
    $amikomSecretKey = substr(md5($amikomSecretKey, true), 0, 24);
    $i = 16;
    for ($i2 = 0; $i2 < 8; $i2++) {
        $amikomSecretKey[$i] = $amikomSecretKey[$i2];
        $i++;
    }
}

function encryptPresensiKode($nim, $code)
{
    global $amikomSecretKey;
    return openssl_encrypt("{$code};{$nim}", 'DES-EDE3', $amikomSecretKey);
}

function decryptPresensiKode($str)
{
    global $amikomSecretKey;
    return openssl_decrypt($str, 'DES-EDE3', $amikomSecretKey);
}

initAmikomSecretKey();
