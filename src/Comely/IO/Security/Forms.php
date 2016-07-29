<?php
declare(strict_types=1);

namespace Comely\IO\Security;

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
     */
    public function create(string $name)
    {

    }
}