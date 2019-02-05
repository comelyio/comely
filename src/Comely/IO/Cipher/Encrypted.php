<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cipher;

use Comely\IO\Cipher\Exception\CipherException;

/**
 * Class Encrypted
 * @package Comely\IO\Cipher
 */
class Encrypted implements \Serializable
{
    /** @var string */
    private $type;
    /** @var string */
    private $data;

    /**
     * Encrypted constructor.
     * @param $data
     * @throws CipherException
     */
    public function __construct($data)
    {
        $this->type = gettype($data);
        switch ($this->type) {
            case "integer":
            case "double":
            case "string":
                $this->data = $data;
                break;
            case "array":
                $this->data = base64_encode(json_encode($data));
                break;
            case "object":
                $this->data = base64_encode(serialize($data));
                break;
            default:
                throw new CipherException(sprintf('Data of type "%s" cannot be encrypted', $this->type));
        }
    }

    /**
     * @return object|array|string|int|float
     * @throws CipherException
     */
    public function withdraw()
    {
        if ($this->type === "object") {
            $unserialize = unserialize(base64_decode($this->data));
            if (!is_object($unserialize)) {
                throw new CipherException('Failed to unserialize encrypted object');
            }

            return $unserialize;
        } elseif ($this->type === "array") {
            $array = json_decode(base64_decode($this->data), true);
            if (!is_array($array)) {
                throw new CipherException('Failed to JSON decode encrypted array');
            }

            return $array;
        }

        return $this->data;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return base64_encode(serialize([
            "type" => $this->type,
            "data" => $this->data
        ]));
    }

    /**
     * @param string $serialized
     * @throws CipherException
     */
    public function unserialize($serialized)
    {
        $unserialize = unserialize(base64_decode($serialized));
        if (!is_array($unserialize)) {
            throw new CipherException('Incomplete or corrupt encrypted data');
        }

        $this->type = $unserialize["type"] ?? null;
        $this->data = $unserialize["data"] ?? null;
        if (!$this->type || !$this->data) {
            throw new CipherException('Incomplete or corrupt encrypted data');
        }
    }
}