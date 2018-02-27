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
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Cipher
 * @package Comely\IO\Cipher
 */
class Cipher implements ComponentInterface, Constants
{
    /** @var Keychain */
    private $keychain;
    /** @var null|CipherKey */
    private $defaultKey;
    /** @var OpenSSL */
    private $openSSL;

    /**
     * Cipher constructor.
     */
    public function __construct()
    {
        if (!extension_loaded("openssl")) {
            throw new CipherException('Cipher requires OpenSSL extension installed and enabled');
        }

        $this->keychain = new Keychain();
        $this->openSSL = new OpenSSL();
    }

    /**
     * @return Keychain
     */
    public function keychain(): Keychain
    {
        return $this->keychain;
    }

    /**
     * @return OpenSSL
     */
    public function openSSL(): OpenSSL
    {
        return $this->openSSL;
    }

    /**
     * @param CipherKey $key
     * @return Cipher
     */
    public function defaultKey(CipherKey $key): self
    {
        $this->defaultKey = $key;
        return $this;
    }

    /**
     * @param $data
     * @param CipherKey|null $key
     * @return string
     * @throws CipherException
     */
    public function encrypt($data, ?CipherKey $key = null): string
    {
        $key = $key ?? $this->defaultKey ?? null;
        if (!$key) {
            throw new CipherException('No default cipher key set, provide an instance of CipherKey');
        }

        $this->checkKey($key); // Validate CipherKey

        $iv = openssl_random_pseudo_bytes($key->_offset);
        if (!$iv) {
            throw new CipherException('Failed to generate Initialization Vector');
        }

        $data = serialize(new Encrypted($data));
        $encrypted = $this->openSSL->encrypt($key, $data, $iv);

        return $this->encode($key, $iv . $encrypted);
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
     * @param string $encrypted
     * @param CipherKey|null $key
     * @return array|float|int|object|string
     * @throws CipherException
     */
    public function decrypt(string $encrypted, ?CipherKey $key = null)
    {
        $key = $key ?? $this->defaultKey ?? null;
        if (!$key) {
            throw new CipherException('No default cipher key set, provide an instance of CipherKey');
        }

        $this->checkKey($key); // Validate CipherKey

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

        $decrypt = $this->openSSL->decrypt($key, $decoded);
        $encrypted = unserialize($decrypt);
        if (!$encrypted || !$encrypted instanceof Encrypted) {
            throw new CipherException('Failed to retrieve encrypted item');
        }

        return $encrypted->withdraw();
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
}