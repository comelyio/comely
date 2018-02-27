<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cipher;

use Comely\IO\Cipher\Exception\CipherException;
use Comely\IO\Cipher\Keychain\CipherKey;

/**
 * Class OpenSSL
 * @package Comely\IO\Cipher
 */
class OpenSSL implements Constants
{
    /**
     * @param CipherKey $key
     * @param string $data
     * @param string $iv
     * @return string
     * @throws CipherException
     */
    public function encrypt(CipherKey $key, string $data, ?string $iv = null): string
    {
        $this->checkKey($key); // Validate CipherKey

        if (!$iv) {
            $iv = openssl_random_pseudo_bytes($key->_offset);
            if (!$iv) {
                throw new CipherException('Failed to generate Initialization Vector');
            }
        }

        $encrypted = openssl_encrypt($data, $key->_cipher, $key->_key, OPENSSL_RAW_DATA, $iv);
        if (!$encrypted) {
            throw new CipherException(sprintf('Failed to encrypt using cipher "%s"', $key->_cipher));
        }

        return $this->encode($key, $iv . $encrypted);
    }

    /**
     * @param CipherKey $key
     * @param string $encrypted
     * @param bool $decode
     * @param null|string $iv
     * @return string
     * @throws CipherException
     */
    public function decrypt(CipherKey $key, string $encrypted, bool $decode = true, ?string $iv = null): string
    {
        $this->checkKey($key); // Validate CipherKey

        if ($decode) {
            $decoded = $this->decode($key, $encrypted);
            if (!$decoded) {
                // Decode attempt 2, auto-detect encoding
                if (preg_match('/^[a-fA-F0-9]+$/', $encrypted)) {
                    $decoded = bin2hex($encrypted);
                } elseif (preg_match('/^[a-zA-Z0-9\_\+]+$/', $encrypted)) {
                    $decoded = base64_decode($encrypted);
                }
            }

            if (!$decoded || !is_string($decoded)) {
                throw new CipherException('Failed to decode encrypted bytes');
            }

            $encrypted = $decoded;
        }

        if (!$iv) {
            $iv = substr($encrypted, 0, $key->_offset);
            $encrypted = substr($encrypted, $key->_offset);
        }

        if (!$iv) {
            throw new CipherException('Failed to retrieve Initialization Vector');
        } elseif (!$encrypted) {
            throw new CipherException('Failed to retrieve encrypted bytes');
        }

        $decrypt = openssl_decrypt($encrypted, $key->_cipher, $key->_key, OPENSSL_RAW_DATA, $iv);
        if (!$decrypt) {
            throw new CipherException('Failed to decrypt data');
        }

        return $decrypt;
    }

    /**
     * @param CipherKey $key
     * @throws CipherException
     */
    private function checkKey(CipherKey $key): void
    {
        if (!$key->_cipher) {
            throw new CipherException('No cipher method defined for provided CipherKey');
        }
    }

    /**
     * @param CipherKey $key
     * @param string $binary
     * @return string
     */
    private function encode(CipherKey $key, string $binary): string
    {
        switch ($key->_encoding) {
            case self::ENCODE_HEXITS:
                return bin2hex($binary);
            case self::ENCODE_BASE64:
            default:
                return base64_encode($binary);
        }
    }

    /**
     * @param CipherKey $key
     * @param string $encoded
     * @return string
     */
    private function decode(CipherKey $key, string $encoded): string
    {
        switch ($key->_encoding) {
            case self::ENCODE_HEXITS:
                return hex2bin($encoded);
            case self::ENCODE_BASE64:
            default:
                return base64_decode($encoded);
        }
    }
}