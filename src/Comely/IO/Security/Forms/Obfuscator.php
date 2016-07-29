<?php
declare(strict_types=1);

namespace Comely\IO\Security\Forms;

use Comely\IO\Security\Security;

/**
 * Class Obfuscator
 * @package Comely\IO\Security
 */
class Obfuscator
{
    const OBFUSCATE_KEY_SIZE    =   12;

    private $name;
    private $obfuscated;
    private $hash;

    /**
     * Obfuscator constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name =   $name;
        $this->obfuscated   =   [];
    }

    /**
     * @param \string[] ...$keys
     * @return Obfuscator
     */
    public function setFields(string ...$keys) : self
    {
        $keysCount  =   count($keys);
        $bitsCount  =   $keysCount*(self::OBFUSCATE_KEY_SIZE*4);

        // Get cryptographically secure random bytes
        $bytes  =   Security::randomKey($bitsCount);
        $bytes  =   str_split($bytes, self::OBFUSCATE_KEY_SIZE);

        // Make sure there are no duplicates some how
        if(count($bytes)    !== count(array_unique($bytes))) {
            // Repeating key detected, retry
            return $this->setFields(...$keys);
        }

        // Iterate through keys
        $count  =   0;
        foreach($keys as $key) {
            $this->obfuscated[$key] =   $bytes[$count];
            $count++;
        }

        // Save hash
        $this->hash =   hash("sha1", array_keys($this->obfuscated));

        // Chain
        return $this;
    }

    /**
     * @return array
     */
    public function getObfuscated() : array
    {
        return $this->obfuscated;
    }

    /**
     * @return string
     */
    public function getHash() : string
    {
        return $this->hash;
    }
}