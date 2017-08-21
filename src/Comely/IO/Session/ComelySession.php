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

namespace Comely\IO\Session;

use Comely\IO\Session\ComelySession\Bag;

/**
 * Class ComelySession
 * @package Comely\Kernel\Http\Session
 */
class ComelySession
{
    /** @var string */
    private $id;
    /** @var Bag */
    private $data;
    /** @var string */
    private $encoded;
    /** @var string */
    private $hash;
    /** @var array */
    private $timeStamp;

    /**
     * ComelySession constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id   =   $id;
        $this->data =   new Bag();
        $this->encoded =   "";
        $this->hash =   "";
        $this->timeStamp    =   [
            "create" => microtime(true),
            "last" => microtime(true)
        ];
    }

    /**
     * Get session ID
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get primary session bag
     *
     * @return Bag
     */
    public function getData() : Bag
    {
        return $this->data;
    }

    /**
     * Alias for getData
     *
     * @return Bag
     */
    public function getBags() : Bag
    {
        return $this->getData();
    }

    /**
     * @param string $id
     * @return ComelySession
     */
    public function setId(string $id) : self
    {
        $this->id   =   $id;
        return $this;
    }

    /**
     * @param Bag $bag
     * @return ComelySession
     */
    public function setData(Bag $bag) : self
    {
        $this->data =   $bag;
        return $this;
    }

    /**
     * Encodes session data
     * For security reasons, session data should be encoded in JSON prior to serialize
     *
     * @param string $salt
     * @param int $cost
     */
    public function encodeData(string $salt, int $cost)
    {
        // Update timeStamp
        $this->timeStamp["last"]  =   microtime(true);

        // Encode JSON data
        $this->encoded =   serialize($this->data);
        $this->hash =   Session::saltedHash($this->encoded, $salt, $cost);
    }

    /**
     * Prepare session for writing
     *
     * @return array
     * @throws SessionException
     */
    public function __sleep() : array
    {
        return ["id","encoded","hash","timeStamp"];
    }

    /**
     * Prepare session for resuming
     */
    public function __wakeup()
    {
        // Check if all properties exist...
        foreach(["id","encoded","hash","timeStamp"] as $property) {
            if(!property_exists($this, $property)) {
                throw SessionException::badWakeUp();
            }
        }
    }

    /**
     * Prepares session after waking up
     *
     * @param int $expiry
     * @param string $salt
     * @param int $cost
     * @return bool
     * @throws SessionException
     */
    public function decodeData(int $expiry, string $salt, int $cost) : bool
    {
        // Check if this session needs decoding
        if(is_array($this->data)    ||  empty($this->encoded)) {
            throw new SessionException(__METHOD__, "Session is already decoded", 1502);
        }

        // Check validity
        $span   =   microtime(true) - $this->timeStamp["last"];
        if($span    >=  $expiry) {
            // Session has expired
            return false;
        }

        // Checksum
        if(!hash_equals(Session::saltedHash($this->encoded, $salt, $cost), $this->hash)) {
            throw new SessionException(__METHOD__, "Session checksum failed", 1503);
        }

        // Decode data
        $this->data =   unserialize($this->encoded, [
            "allowed_classes" => [
                "Comely\\IO\\Session\\ComelySession\\Bag"
            ]
        ]);
        if(!$this->data instanceof Bag) {
            throw new SessionException(__METHOD__, "Failed to un-serialize data bags", 1504);
        }

        // Release "encoded" property
        $this->encoded  =   null;

        // Successfully decoded
        return true;
    }
}