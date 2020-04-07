<?php

$amikomSecretKey = $amikomXApiKey = "";

function initAmikomSecretKey()
{
    global $amikomSecretKey, $amikomXApiKey;
    loadConfig("amikom");
    $amikomXApiKey = AMIKOM_X_API_KEY;
    $amikomSecretKey = substr(md5(AMIKOM_SECRET_KEY, true), 0, 24);
    
    for ($i = 16, $i2 = 0; $i2 < 8; $i2++, $i++)
        $amikomSecretKey[$i] = $amikomSecretKey[$i2];
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
