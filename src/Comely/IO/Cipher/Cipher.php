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

        $data = serialize(new Encrypted($data));
        return $this->openSSL->encrypt($key, $data, null);
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

        $decrypt = $this->openSSL->decrypt($key, $encrypted, true, null);
        $encrypted = unserialize($decrypt);
        if (!$encrypted || !$encrypted instanceof Encrypted) {
            throw new CipherException('Failed to retrieve encrypted item');
        }

        return $encrypted->withdraw();
    }
}