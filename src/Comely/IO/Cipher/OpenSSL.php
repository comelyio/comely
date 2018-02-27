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
class OpenSSL
{
    /**
     * @param CipherKey $key
     * @param string $data
     * @param string $iv
     * @return string
     * @throws CipherException
     */
    public function encrypt(CipherKey $key, string $data, string $iv): string
    {
        $encrypt = openssl_encrypt($data, $key->_cipher, $key->_key, OPENSSL_RAW_DATA, $iv);
        if (!$encrypt) {
            throw new CipherException(sprintf('Failed to encrypt using cipher "%s"', $key->_cipher));
        }

        return $encrypt;
    }

    /**
     * @param CipherKey $key
     * @param string $raw
     * @param string $iv
     * @return string
     * @throws CipherException
     */
    public function decrypt(CipherKey $key, string $raw, string $iv): string
    {
        $decrypt = openssl_decrypt($raw, $key->_cipher, $key->_key, OPENSSL_RAW_DATA, $iv);
        if (!$decrypt) {
            throw new CipherException('Failed to decrypt data');
        }

        return $decrypt;
    }
}