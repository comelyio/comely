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

namespace Comely\IO\HttpRouter\Controller\Data;

use Comely\IO\HttpRouter\Router;

/**
 * Class Payload
 * @package Comely\IO\HttpRouter\Controller\Data
 */
class Payload extends AbstractIterableData
{
    /**
     * Payload constructor.
     * @param Router $router
     * @param array|null $payload
     */
    public function __construct(Router $router, ?array $payload = null)
    {
        parent::__construct($router);
        if (is_array($payload)) {
            $sanitized = $router->sanitizer()->payload($payload);
            foreach ($sanitized as $key => $value) {
                $this->push(strval($key), $value);
            }
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    private function push(string $key, $value): bool
    {
        return $this->setProp(new Property($key, $value));
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        if (!preg_match('/^[a-zA-Z0-9\s\_\-\.]+$/', $key)) {
            return false;
        }

        return $this->push($key, $value);
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function get(string $key): ?string
    {
        $prop = $this->getProp($key);
        return $prop->value ?? null;
    }
}