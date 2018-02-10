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

namespace Comely\IO\HttpRouter\Router;

use Comely\IO\HttpRouter\Exception\SanitizerException;

/**
 * Class Sanitizer
 * @package Comely\IO\HttpRouter\Router
 */
class Sanitizer
{
    public const UTF8 = "utf8";
    public const ASCII = "ascii";

    /** @var string */
    private $encoding;

    /**
     * Sanitizer constructor.
     * @param null|string $encoding
     * @throws SanitizerException
     */
    public function __construct(?string $encoding = null)
    {
        $this->encoding($encoding ?? self::UTF8);
    }

    /**
     * @param string $enc
     * @return Sanitizer
     * @throws SanitizerException
     */
    public function encoding(string $enc): self
    {
        if (!in_array($enc, [self::UTF8, self::ASCII])) {
            throw new SanitizerException('Invalid encoding type for sanitizing inbound data');
        }

        return $this;
    }

    /**
     * @param array $input
     * @return array
     */
    public function payload(array $input): array
    {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $key)) {
                continue; // Not a valid key
            }

            if (is_scalar($value)) {
                if (is_string($value)) {
                    switch ($this->encoding) {
                        case self::ASCII:
                            $value = filter_var(
                                $value,
                                FILTER_SANITIZE_STRING,
                                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                            );
                            break;
                        case self::UTF8:
                        default:
                            $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                    }
                }

                $sanitized[$key] = $value;
            } else {
                if (is_array($value)) {
                    $sanitized[$key] = $this->payload($value); // Recursively sanitize arrays
                }
            }
        }

        return $sanitized;
    }

    /**
     * @param array $headers
     * @return array
     */
    public function headers(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-zA-Z0-9\s\_\-\.]+$/', $key)) {
                continue; // Not a valid key
            }

            if (!is_string($value)) {
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}