<?php
declare(strict_types=1);

namespace Comely\IO\Security;

use Comely\IO\Session\ComelySession\Bag;

/**
 * Class CSRF
 * @package Comely\IO\Security
 */
class CSRF
{
    private $sessionBag;

    /**
     * CSRF constructor.
     * @param Bag $bag
     */
    public function __construct(Bag $bag)
    {
        $this->sessionBag   =   $bag;
    }

    /**
     * @param int $expire
     * @return string
     * @throws SecurityException
     */
    public function setToken(int $expire = 0) : string
    {
        // Set expiry for token?
        if($expire  >   0) {
            // Add time stamp if expire is > 0
            $expire +=  time();
        }

        // Securely generate random CSRF token
        // 160 bits = 40 (hexadecimal) characters
        $token  =   Security::randomKey(160);

        // Write token to session bag
        $this->sessionBag
            ->set("token", $token)
            ->set("expire", $expire);

        // Return token
        return $token;
    }

    /**
     * @return string
     */
    public function getToken() : string
    {
        $token  =   $this->sessionBag->get("token");
        $expire =   $this->sessionBag->get("expire");

        // Check expire prop. data type and if its > 0
        if(is_int($expire)  &&  $expire >   0) {
            // Check if token is expired
            if(time()   >=  $expire) {
                $token  =   null; // Expired
                $this->sessionBag->remove("token")->remove("expire");
            }
        }

        // Return token or an empty string
        return $token ?? "";
    }

    /**
     * @param string $userProvided
     * @return bool
     */
    public function verify(string $userProvided) : bool
    {
        return hash_equals($this->getToken(), $userProvided);
    }
}