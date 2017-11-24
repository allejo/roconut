<?php

namespace AppBundle\Service;

class Crypto
{
    public static function encrypt_v1($data, $key)
    {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);

        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

        // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        return base64_encode('v1::' . $encrypted . '::' . $iv);
    }

    public static function decrypt_v1($data, $key)
    {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);

        // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
        list($version, $encrypted_data, $iv) = explode('::', base64_decode($data), 3);

        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
}
