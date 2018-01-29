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

namespace Comely\Engine\Extend;

use Comely\Engine\Comely;

/**
 * Interface ComponentInterface
 * @package Comely\Engine\Extend
 */
interface ComponentInterface
{
    public const VERSION    =   Comely::VERSION;
    public const VERSION_ID =   Comely::VERSION_ID;
}