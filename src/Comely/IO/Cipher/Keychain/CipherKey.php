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

namespace Comely\IO\Cipher\Keychain;

use Comely\IO\Cipher\Constants;
use Comely\IO\Cipher\Exception\CipherKeyException;
use Comely\IO\Cipher\Exception\DigestException;
use Comely\Kernel\Toolkit\Number;

/**
 * Class CipherKey
 * @package Comely\IO\Cipher\Keychain
 * @property string $_cipher
 * @property string $_key
 * @property int $_offset
 * @property int $_encoding
 */
class CipherKey implements Constants
{
    /** @var string */
    private $key;
    /** @var int */
    private $encoding;
    /** @var null|string */
    private $cipherMethod;
    /** @var null|int */
    private $ivLength;

    /**
     * CipherKey constructor.
     * @param string $hexits
     * @throws CipherKeyException
     */
    public function __construct(string $hexits)
    {
        // Validate key
        if (!ctype_xdigit($hexits)) {
            throw new CipherKeyException('Cipher key must be comprised of hexadecimal digits');
        }

        $binary = strval(hex2bin($hexits));
        $bitLength = strlen($binary) * 8;
        if (!in_array($bitLength, self::KEY_SIZES)) {
            throw new CipherKeyException(
                sprintf(
                    'Key of size %d bits not acceptable, use a %s bits long key',
                    $bitLength,
                    implode(",", self::KEY_SIZES)
                )
            );
        }

        $this->key = $binary;
        $this->encoding = self::ENCODE_BASE64;
    }

    /**
     * @param string $cipherMethod
     * @return CipherKey
     * @throws CipherKeyException
     */
    public function cipherMethod(string $cipherMethod = "aes-256-cbc"): self
    {
        // Cipher method
        if (!in_array($cipherMethod, openssl_get_cipher_methods())) {
            throw new CipherKeyException(
                sprintf('Cipher method "%s" is not supported by OpenSSL ext.', $cipherMethod)
            );
        }

        $this->cipherMethod = $cipherMethod;
        $this->ivLength = openssl_cipher_iv_length($this->cipherMethod);
        return $this;
    }

    /**
     * @param int $flag
     * @return CipherKey
     * @throws CipherKeyException
     */
    public function encoding(int $flag): self
    {
        if (!in_array($flag, [self::ENCODE_BASE64, self::ENCODE_HEXITS])) {
            throw new CipherKeyException('Invalid encoding flag, use a valid Cipher::ENCODE_* flag');
        }

        $this->encoding = $flag;
        return $this;
    }

    /**
     * @param $prop
     * @return int|string
     * @throws CipherKeyException
     */
    public function __get($prop)
    {
        switch (strtolower($prop)) {
            case "_cipher":
                return $this->cipherMethod;
            case "_key":
                return $this->key;
            case "_offset":
                return $this->ivLength;
            case "_encoding":
                return $this->encoding;
        }

        throw new CipherKeyException('Cannot read inaccessible properties');
    }

    /**
     * @param $prop
     * @param $value
     * @throws CipherKeyException
     */
    public function __set($prop, $value)
    {
        throw new CipherKeyException('Cannot write inaccessible properties');
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            "Private Cipher Key",
            $this->cipherMethod
        ];
    }

    /**
     * @param string $data
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * @return string
     * @throws DigestException
     */
    public function hash(string $data, string $salt, int $iterations = 1, int $length = 40): string
    {
        if (!Number::Range($length, 1, 128)) {
            throw new DigestException('Invalid hash digest length, a digest can be up to 128 character longs');
        }

        if ($iterations < 1) {
            throw new DigestException('Iterations parameter must be a positive integer');
        }

        $bytes = intval($length / 2) + 1; // hexits to bytes
        $algo = "sha1";
        if ($bytes > 32) {
            $algo = "sha512";
        } elseif ($bytes > 20) {
            $algo = "sha256";
        }

        // Iterations
        $digestSalt = openssl_digest($this->key . $salt, "sha1", true);
        $digest = openssl_digest($data . $digestSalt, $algo, true);
        for ($i = 0; $i < $iterations; $i++) {
            if (!$digestSalt || !$digest) {
                break; // Failure
            }

            $digestSalt = openssl_digest($digestSalt . ($i * $iterations), "sha1", true);
            $digest = openssl_digest($digest . $digestSalt, $algo, true);
        }

        // Final check
        if (!$digestSalt || !$digest) {
            throw new DigestException('Failed to compute digest');
        }

        // Trim to length
        return substr(bin2hex($digest), 0, $length);
    }
}