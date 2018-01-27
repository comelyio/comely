<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Emails\Mailer;

use Comely\IO\Emails\Message;

/**
 * Class Sendmail
 * @package Comely\IO\Emails\Mailer
 */
class Sendmail implements AgentInterface
{
    /**
     * @param Message $message
     * @param array $emails
     * @return int
     */
    public function send(Message $message, array $emails) : int
    {
        $separator  =   sprintf('--MIME-SEPARATOR-%1$s', microtime(false));
        $messageMime    =   explode($separator, $message->getCompiled($separator));

        $sendMail   =   mail(
            implode(",", $emails),
            $message->getSubject(),
            $messageMime[1],
            $messageMime[0]
        );

        return $sendMail ? count($emails) : 0;
    }
}