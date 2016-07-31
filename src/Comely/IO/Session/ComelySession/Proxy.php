<?php
declare(strict_types=1);

namespace Comely\IO\Session\ComelySession;

use Comely\IO\Session\ComelySession;

/**
 * Class Proxy
 * @package Comely\IO\Session\ComelySession
 */
class Proxy
{
    /**
     * @var ComelySession
     */
    private $instance;

    /**
     * Proxy constructor.
     * @param ComelySession $instance
     */
    public function __construct(ComelySession $instance)
    {
        $this->setInstance($instance);
    }

    /**
     * @param ComelySession $instance
     * @return Proxy
     */
    public function setInstance(ComelySession $instance) : self
    {
        $this->instance =   $instance;
        return $this;
    }

    /**
     * @return ComelySession
     */
    public function getInstance() : ComelySession
    {
        return $this->instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->instance,$name], $arguments);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->instance->$name;
    }

    /**
     * @return Bag
     */
    public function getBags() : Bag
    {
        return $this->instance->getBags();
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->instance->getId();
    }

    /**
     * @param string $salt
     * @param int $cost
     */
    public function encodeData(string $salt, int $cost)
    {
        $this->instance->encodeData($salt, $cost);
    }

    /**
     * @param int $expiry
     * @param string $salt
     * @param int $cost
     * @return bool
     */
    public function decodeData(int $expiry, string $salt, int $cost) : bool
    {
        return $this->instance->decodeData($expiry, $salt, $cost);
    }
}