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

namespace Comely\IO\HttpRouter\Controller\Data;

use Comely\IO\HttpRouter\Router;

/**
 * Class Headers
 * @package Comely\IO\HttpRouter\Controller\Data
 */
class Headers extends AbstractIterableData
{
    /**
     * Headers constructor.
     * @param Router $router
     * @param array|null $headers
     */
    public function __construct(Router $router, ?array $headers = null)
    {
        parent::__construct($router);
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        $prop = $this->router->sanitizer()->header($key, $value);
        if ($prop) {
            return $this->setProp($prop);
        }

        return false;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function get(string $key): ?string
    {
        return $this->getProp($key)->value ?? null;
    }
}