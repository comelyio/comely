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

namespace Comely\IO\Security\Forms;

use Comely\IO\Security\Security;
use Comely\IO\Session\ComelySession\Bag;

/**
 * Class Obfuscator
 * @package Comely\IO\Security
 */
class Obfuscator implements SecureFormsInterface
{
    const OBFUSCATE_KEY_SIZE    =   12;

    /** @var null|string */
    private $hash;
    /** @var string */
    private $name;
    /** @var array */
    private $obfuscated;
    /** @var Bag|null */
    private $sessionBag;

    /**
     * Obfuscator constructor.
     * @param string $name
     * @param Bag|null $bag
     */
    public function __construct(string $name, Bag $bag = null)
    {
        $this->name =   $name;
        $this->obfuscated   =   [];
        $this->sessionBag   =   $bag;
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
            return call_user_func_array([$this, "setFields"], $keys);
        }

        // Iterate through keys
        $count  =   0;
        foreach($keys as $key) {
            $obfuscated =   $bytes[$count];
            if(preg_match('/^[0-9]+$/', $obfuscated)) {
                $obfuscated{0}  =   "a";
            }
            $this->obfuscated[$key] =   $obfuscated;
            $count++;
        }

        // Save hash
        $this->hash =   hash("sha1", implode(":", array_keys($this->obfuscated)));

        // Save to session?
        if(isset($this->sessionBag)) {
            $this->sessionBag->getBag($this->name)
                ->set("hash", $this->hash)
                ->set("fields", $this->obfuscated);
        }

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

    /**
     * @return array
     */
    public function getArray() : array
    {
        return [
            "hash"  =>  $this->hash,
            "fields"    =>  $this->obfuscated
        ];
    }
}