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

namespace Comely\IO\Emails\Exception;

use Comely\IO\Emails\EmailsException;

/**
 * Class MailerException
 * @package Comely\IO\Emails\Mailer\Exception
 */
class MailerException extends EmailsException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Emails\\Mailer";
}