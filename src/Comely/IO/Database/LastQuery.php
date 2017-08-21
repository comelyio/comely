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

namespace Comely\IO\Database;

/**
 * Class LastQuery
 * @package Comely\IO\Database
 */
class LastQuery
{
    /** @var string|null */
    public $query;
    /** @var int */
    public $rows;
    /** @var string|null */
    public $error;

    /**
     * LastQuery constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Resets LastQuery object
     */
    public function reset()
    {
        $this->query    =   null;
        $this->rows =   0;
        $this->error    =   null;
    }
}