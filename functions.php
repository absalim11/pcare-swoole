<?php
use LZCompressor\LZString;

function stringDecrypt($key, $string){
    echo "Decrypting...\n";
    $encrypt_method = 'AES-256-CBC';
    // hash
    $key_hash = hex2bin(hash('sha256', $key));
    $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
    echo "Decryption complete.\n";
    return $output;
}

function decompress($string){
    echo "Decompressing...\n";
    $output = LZString::decompressFromEncodedURIComponent($string);
    echo "Decompression complete.\n";
    return $output;
}

?>