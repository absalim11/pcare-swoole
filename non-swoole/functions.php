<?php
/**
 * Helper Functions for Traditional PHP Implementation
 * Decrypt and Decompress functions
 */

use LZCompressor\LZString;

/**
 * Decrypt string using AES-256-CBC
 *
 * @param string $key Encryption key
 * @param string $string Encrypted string (base64 encoded)
 * @return string Decrypted string
 */
function stringDecrypt($key, $string) {
    $encrypt_method = 'AES-256-CBC';

    // hash
    $key_hash = hex2bin(hash('sha256', $key));
    $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

    $output = openssl_decrypt(
        base64_decode($string),
        $encrypt_method,
        $key_hash,
        OPENSSL_RAW_DATA,
        $iv
    );

    return $output;
}

/**
 * Decompress LZ-String compressed data
 *
 * @param string $string Compressed string
 * @return string Decompressed string
 */
function decompress($string) {
    $output = LZString::decompressFromEncodedURIComponent($string);
    return $output;
}
