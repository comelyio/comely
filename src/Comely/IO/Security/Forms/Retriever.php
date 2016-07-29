<?php
declare(strict_types=1);

namespace Comely\IO\Security\Forms;

/**
 * Class Retriever
 * @package Comely\IO\Security\Forms
 */
class Retriever
{
    private $input;
    private $name;
    private $obfuscated;

    /**
     * Retriever constructor.
     * @param string $name
     * @param array $obfuscated
     */
    public function __construct(string $name, array $obfuscated)
    {
        $this->name =   $name;
        $this->obfuscated   =   $obfuscated;
        $this->input    =   [];
    }

    /**
     * @param string $userProvided
     * @return bool
     */
    public function checkHash(string $userProvided) : bool
    {
        return hash_equals(
            hash("sha1", array_keys($this->obfuscated)),
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
    public function getValue(string $key)
    {
        // null coalesce operator here suppresses E_NOTICE if $key is not found in input array
        return $this->input[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getObfuscated() : array
    {
        return $this->obfuscated;
    }
}