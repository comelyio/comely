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

use Comely\IO\Cipher\Exception\KeychainException;
use Comely\IO\Cipher\Keychain\CipherKey;

/**
 * Class Keychain
 * @package Comely\IO\Cipher
 */
class Keychain
{
    /** @var array */
    private $keys;

    /**
     * Keychain constructor.
     */
    public function __construct()
    {
        $this->keys = [];
    }

    /**
     * @param string $tag
     * @param CipherKey $cipherKey
     */
    public function add(string $tag, CipherKey $cipherKey): void
    {
        $this->keys[$tag] = $cipherKey;
    }

    /**
     * @param int $bits
     * @return CipherKey
     * @throws KeychainException
     */
    public function generate(int $bits): CipherKey
    {
        if ($bits % 8 != 0) {
            throw new KeychainException('Bit length must be divisible by 8 to generate new key');
        }

        try {
            $randomBytes = random_bytes(intval(($bits / 8)));
        } catch (\Exception $e) {
            throw new KeychainException('Failed to generate a new key');
        }

        return new CipherKey(bin2hex($randomBytes));
    }

    /**
     * @param string $tag
     * @return CipherKey
     * @throws KeychainException
     */
    public function get(string $tag): CipherKey
    {
        $key = $this->keys[$tag] ?? null;
        if (!$key) {
            throw new KeychainException(sprintf('Cipher key "%s" not stored in keychain', $tag));
        }

        return $key;
    }
}