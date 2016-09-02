<?php
declare(strict_types=1);

namespace Comely\IO\Security\Forms;

/**
 * Class Retriever
 * @package Comely\IO\Security\Forms
 */
class Retriever implements \Countable
{
    private $input;
    private $name;
    private $obfuscated;
    private $count;

    /**
     * Retriever constructor.
     * @param string $name
     * @param array $obfuscated
     */
    public function __construct(string $name, array $obfuscated)
    {
        $this->name =   $name;
        $this->obfuscated   =   $obfuscated;
        $this->count    =   count($obfuscated);
        $this->input    =   [];
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function getHash() : string
    {
        return hash("sha1", implode(":", array_keys($this->obfuscated)));
    }

    /**
     * @param string $userProvided
     * @return bool
     */
    public function checkHash(string $userProvided) : bool
    {
        return hash_equals(
            $this->getHash(),
            $userProvided
        );
    }

    /**
     * @param array $data
     * @return Retriever
     */
    public function inputSource(array &$data) : self
    {
        $this->input    =   &$data;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        if(array_key_exists($key, $this->obfuscated)) {
            // null coalesce operator here suppresses E_NOTICE if $key is not found in input array
            return $this->input[$this->obfuscated[$key]] ?? null;
        }

        return null;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getValue(string $key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function key(string $key)
    {
        // null coalesce operator here suppresses E_NOTICE if $key is not found in input array
        return $this->obfuscated[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getObfuscated() : array
    {
        return $this->obfuscated;
    }
}