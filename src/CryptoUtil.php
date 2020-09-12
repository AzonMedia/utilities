<?php

declare(strict_types=1);

namespace Azonmedia\Utilities;

use Azonmedia\Exceptions\RunTimeException;
use Azonmedia\Translator\Translator as t;

abstract class CryptoUtil
{

    public const OPENSSL_ENCRYPTION_METHOD = 'AES-256-OFB';//AES-256-CBC
    public const INITIALIZATION_VECTOR_LENGTH = 16;

    /**
     * @param string $plain_string
     * @param string $key
     * @param string $encryption_method
     * @return string
     */
    public static function openssl_encrypt(string $plain_string, string $key, string $encryption_method = self::OPENSSL_ENCRYPTION_METHOD): string
    {
        $iv = StringUtil::generate_random_string(self::INITIALIZATION_VECTOR_LENGTH );//initialization vector
        //$ret = base64_encode(openssl_encrypt($plain_string, $encryption_method, substr(sha1($key), 0, 32), 0, $iv )) . $iv;
        $ret = StringUtil::encode_base64_url_safe(openssl_encrypt($plain_string, $encryption_method, substr(sha1($key), 0, 32), 0, $iv ) . $iv );
        return $ret;
    }

    /**
     * @param string $encrypted_string
     * @param string $key
     * @param string $encryption_method
     * @return string
     */
    public static function openssl_decrypt(string $encrypted_string, string $key, string $encryption_method = self::OPENSSL_ENCRYPTION_METHOD): ?string
    {
        $encrypted_string = StringUtil::decode_base64_url_safe($encrypted_string);
        if ($encrypted_string === false) {
            return null;
        }
        $iv = substr($encrypted_string, -16);// we need to extract the initialization vector from the encoded string
        $encrypted_string = substr($encrypted_string, 0, strlen($encrypted_string) - 16);
        //$ret = openssl_decrypt( base64_decode($encrypted_string), $encryption_method, substr(sha1($key), 0, 32), 0, $iv );
        $ret = openssl_decrypt( $encrypted_string, $encryption_method, substr(sha1($key), 0, 32), 0, $iv );
        return $ret;
    }

    /**
     * Encrypts data with the public key
     * @param string $data
     * @return string The encrypted content
     */
    public function openssl_public_encrypt(string $plaint_text, string $public_key_path): string
    {
        $public_key = openssl_pkey_get_public(file_get_contents($public_key_path));//PEM
        openssl_public_encrypt($plaint_text, $crypted_text, $public_key);
        $crypted_text = base64_encode($crypted_text);
        return $crypted_text;
    }

    /**
     * Decrypts the data encrypted with the public key by using the private key. If the private key is password protected a password needs to be provided.
     * @param string $crypted_text
     * @param string $certificate_pass
     * @return string
     * @throws framework\openssl\exceptions\wrongPasswordException
     */
    public function decrypt($crypted_text, string $private_key_path, string $certificate_pass = ''): string
    {
        $private_key = openssl_pkey_get_private(file_get_contents($private_key_path), $certificate_pass);
        if ($private_key === false) {
            if ($certificate_pass) {
                throw new RunTimeException(sprintf(t::_('The provided private certificate decryption password is not correct.')));
            } else {
                throw new RunTimeException(sprintf(t::_('This private certificate is encrypted. A decryption password needs to be provided in order to use the private certificate.')));
            }
        }
        $crypted_text = base64_decode($crypted_text);
        openssl_private_decrypt($crypted_text, $decrypted_text, $private_key);
        return $decrypted_text;
    }


}