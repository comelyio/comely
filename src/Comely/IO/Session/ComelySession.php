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

namespace Comely\IO\Session;

use Comely\IO\Session\ComelySession\Bag;

/**
 * Class ComelySession
 * @package Comely\IO\Session
 */
class ComelySession
{
    /** @var string */
    private $id;
    /** @var Bag */
    private $bags;
    /** @var int */
    private $timeStamp;

    /**
     * ComelySession constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->bags = new Bag();
        $this->timeStamp = time();
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return Bag
     */
    public function bags(): Bag
    {
        return $this->bags;
    }

    /**
     * @return int
     */
    public function touch(): int
    {
        return $this->timeStamp = time();
    }
}