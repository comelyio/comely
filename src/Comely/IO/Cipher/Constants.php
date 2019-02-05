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

/**
 * Interface Constants
 * @package Comely\IO\Cipher
 */
interface Constants
{
    public const KEY_SIZES = [256, 512, 1024];
    public const ENCODE_BASE64 = 1001;
    public const ENCODE_HEXITS = 1002;
}