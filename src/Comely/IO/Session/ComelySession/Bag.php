<?php
declare(strict_types=1);

namespace Comely\IO\Session\ComelySession;
use Comely\IO\Session\Exception\ComelySessionException;

/**
 * Class Bag
 * @package Comely\IO\Session\ComelySession
 */
class Bag
{
    private $data;
    private $bags;

    /**
     * Bag constructor.
     */
    public function __construct()
    {
        $this->data =   [];
        $this->bags =   [];
    }

    /**
     * Set a key/value pair
     *
     * @param string $key
     * @param $val
     * @return Bag
     * @throws ComelySessionException
     */
    public function set(string $key, $val) : self
    {
        // Check value type
        if(!is_scalar($val) &&  !is_null($val)  &&  !is_array($val)) {
            throw ComelySessionException::badPropValue(__METHOD__, gettype($val));
        }

        $this->data[$key]   =   $val;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    /**
     * @param \string[] ...$keys
     * @return bool
     */
    public function has(string ...$keys) : bool
    {
        foreach($keys as $key) {
            if(!array_key_exists($key, $this->data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @return Bag
     */
    public function remove(string $key) : self
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Get existing bag or create new one
     *
     * @param string $bag
     * @return Bag
     */
    public function getBag(string $bag) : self
    {
        // Check if bag already exists
        if(array_key_exists($bag, $this->bags)) {
            return $this->bags[$bag];
        }

        // Create bag
        $this->bags[$bag]   =   new self();
        return $this->bags[$bag];
    }

    /**
     * @param string $bag
     * @return bool
     */
    public function hasBag(string $bag) : bool
    {
        return array_key_exists($bag, $this->bags);
    }

    /**
     * @param string $bag
     * @return Bag
     */
    public function removeBag(string $bag) : self
    {
        unset($this->bags[$bag]);
        return $this;
    }

    /**
     * Deletes all data and child bags
     * @return Bag
     */
    public function flush() : self
    {
        $this->data =   [];
        $this->bags =   [];
        
        return $this;
    }

    /**
     * Get data of this bag and all child bags as Array
     * Caution: Child bags will override key/value pairs if keys conflict
     *
     * @return array
     */
    public function getArray() : array
    {
        $array  =   $this->data;
        foreach($this->bags as $key => $bag) {
            $array[$key]    =   $bag->getArray();
        }

        return $array;
    }
}