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
}