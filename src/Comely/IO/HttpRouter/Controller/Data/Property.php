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

/**
 * Class Property
 * @package Comely\IO\HttpRouter\Controller\Data
 */
class Property
{
    /** @var string */
    public $key;
    /** @var mixed */
    public $value;

    /**
     * Property constructor.
     * @param string $key
     * @param $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}