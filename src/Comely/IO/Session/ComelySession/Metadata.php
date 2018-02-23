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

namespace Comely\IO\Session\ComelySession;

use Comely\IO\Session\Exception\ComelySessionException;

/**
 * Class Metadata
 * @package Comely\IO\Session\ComelySession
 */
class Metadata
{
    /** @var array */
    private $props;

    /**
     * Metadata constructor.
     */
    public function __construct()
    {
        $this->props = [];
    }

    /**
     * @param string $key
     * @param $value
     * @return Metadata
     */
    public function set(string $key, $value): self
    {
        $valueType = gettype($value);
        switch ($valueType) {
            case "boolean":
            case "integer":
            case "double":
            case "string":
            case "NULL":
                return $this->props[strtolower($key)] = $value;
            default:
                throw new ComelySessionException(sprintf('Metadata type "%s" cannot be stored', $valueType));
        }
    }

    /**
     * @param string $prop
     * @return mixed|null
     */
    public function get(string $prop)
    {
        return $this->props[strtolower($prop)] ?? null;
    }

    /**
     * Checks if 1 or all the keys exist in Bag
     * @param string[] ...$props
     * @return bool
     */
    public function has(string ...$props): bool
    {
        foreach ($props as $prop) {
            if (!array_key_exists(strtolower($prop), $this->props)) {
                return false;
            }
        }

        return true;
    }
}