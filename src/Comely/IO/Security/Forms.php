<?php
declare(strict_types=1);

namespace Comely\IO\Security;

use Comely\IO\Security\Forms\Obfuscator;
use Comely\IO\Security\Forms\Retriever;
use Comely\IO\Session\ComelySession\Bag;

/**
 * Class Forms
 * @package Comely\IO\Security
 */
class Forms
{
    private $sessionBag;

    /**
     * Forms constructor.
     * @param Bag $bag
     */
    public function __construct(Bag $bag)
    {
        $this->sessionBag   =   $bag;
    }

    /**
     * @param string $name
     * @return Obfuscator
     */
    public function obfuscate(string $name) : Obfuscator
    {
        $obfuscate  =   new Obfuscator($name, $this->sessionBag);
        return $obfuscate;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        // Check if we have form in session bag
        if($this->sessionBag->hasBag($name)) {
            // Get all fields
            $obfuscated =   $this->sessionBag->getBag($name)
                ->get("fields");

            // Form must have at least 1 field
            if(is_array($obfuscated)    &&  count($obfuscated)  >   0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return Retriever
     */
    public function retrieve(string $name) : Retriever
    {
        $obfuscated =   [];
        // Check if form was saved in session
        if($this->sessionBag->hasBag($name)) {
            // Retrieve from session
            $obfuscated =   $this->sessionBag->getBag($name)
                ->get("fields");
            
            // Make sure $obfuscated is an Array
            if(!is_array($obfuscated)) {
                $obfuscated =   [];
                $this->sessionBag->removeBag($name);
            }
        }

        $retriever  =   new Retriever($name, $obfuscated);
        return $retriever;
    }

    /**
     * @param string $name
     * @return Forms
     */
    public function remove(string $name) : self
    {
        $this->sessionBag->removeBag($name);
        return $this;
    }

    /**
     * @return Forms
     */
    public function flush() : self
    {
        $this->sessionBag->flush();
        return $this;
    }
}