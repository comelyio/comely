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

namespace Comely\IO\Cache;

/**
 * Class Profile
 * @package Comely\IO\Cache
 */
class Profile
{
    /** @var string */
    public $type;
    /** @var string */
    public $data;
    /** @var string|null */
    public $instanceOf;

    /**
     * Profile constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->type =   gettype($data);
        if($this->type  === "object") {
            $this->instanceOf   =   get_class($data);
        }

        $this->data =   base64_encode(serialize($data));
    }

    /**
     * @throws CacheException
     */
    public function withdraw()
    {
        $data   =   unserialize(base64_decode($this->data));
        if(gettype($data)   !== $this->type) {
            throw new \DomainException(sprintf('Expecting "%1$s" got "%2$s"', $this->type, gettype($data)));
        }

        if($this->type  === "object") {
            if(!is_a($data, $this->instanceOf)) {
                throw new \DomainException(
                    sprintf(
                        'Expecting instance of "%1$s" got "%2$s"',
                        $this->instanceOf,
                        get_class($data)
                    )
                );
            }
        }

        return $data;
    }
}