<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Emails;

/**
 * Class EmailsException
 * @package Comely\IO\Emails
 */
class EmailsException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $method
     * @return EmailsException
     */
    public static function badEmailAddress(string $method) : self
    {
        return new self(
            $method,
            sprintf('Invalid e-mail address'),
            1001
        );
    }
}