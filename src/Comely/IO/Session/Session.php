<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Session;

use Comely\IO\Session\Storage\StorageInterface;
use ComelyException;
use Comely\IO\Security\Cipher;

/**
 * Class Session
 * @package Comely\IO\Session
 */
class Session
{
    /** @var StorageInterface */
    private $storage;
    /** @var ComelySession */
    private $session;
    /** @var Config */
    private $config;

    /**
     * Session constructor.
     * @param StorageInterface $storage
     * @throws SessionException
     */
    public function __construct(StorageInterface $storage)
    {
        $this->config   =   new Config(); // Load Config
        $this->storage  =   $storage; // Set StorageInterface
    }

    /**
     * @param string|null $id
     * @return string
     * @throws SessionException
     */
    public function start(string $id = null) : string
    {
        // Check if session has already been started
        if($this->session instanceof ComelySession) {
            throw SessionException::sessionExists();
        }

        // Read Session
        $sessionId  =   $id ?? $_COOKIE["COMELYSESSID"] ?? null;
        if(isset($sessionId)) {
            // Session ID found, attempt to read
            $this->resume($sessionId);
        }

        // Check if we got ComelySession from read
        if(!$this->session instanceof ComelySession) {
            $this->create();
        }

        // Register shutdown handler
        register_shutdown_function([$this,"write"]);

        // Save cookie
        $this->sessionCookie($this->session->getId());

        // Return session Id
        return $this->session->getId();
    }

    /**
     * Get reference to ComelySession instance
     * 
     * @return ComelySession
     */
    public function getSession() : ComelySession
    {
        return $this->session;
    }

    /**
     * Resume a ComelySession
     *
     * @param string $id
     */
    private function resume(string $id)
    {
        try {
            // Read from Storage, expect a String
            $session    =   $this->storage->read($id);

            // Cipher Encryption?
            if($this->config->cipher instanceof Cipher) {
                $session    =   $this->config->cipher->decrypt($session);
            }

            // Un-serialize object
            $session    =   @unserialize($session, [
                "allowed_classes"   =>    [
                    "Comely\\IO\\Session\\ComelySession",
                    "Comely\\IO\\Session\\ComelySession\\Bag"
                ]
            ]);

            if($session instanceof ComelySession) {
                try {
                    $decode =   $session->decodeData(
                        $this->config->sessionLife,
                        $this->config->hashSalt,
                        $this->config->hashCost
                    );
                } catch(SessionException $e) {
                    throw $e;
                }
                
                // If session is expired, no exception is thrown, just boolean FALSE is returned
                if(isset($decode)   &&  $decode  === true) {
                    // Save ComelySession reference
                    $this->session  =   $session;
                } else {
                    // Delete session from storage
                    $this->delete($id, true);
                }
            }
        } catch(\Throwable $t) {
            // Session resume failed, trigger an error
            $methodString    =   method_exists($t, "getMethod") ? sprintf("%s: ", $t->getMethod()) :  "";
            trigger_error(
                $methodString . $t->getMessage(),
                E_USER_WARNING
            );
        }
    }

    /**
     * Create a ComelySession
     *
     * @throws SessionException
     */
    private function create()
    {
        // Create secure session ID
        $sessionId  =   $this->generateId();
        if(!$this->isUniqueId($sessionId)) {
            $this->create();
            return;
        }

        // Create new instance of ComelySession
        $this->session    =   new ComelySession($sessionId);
    }

    /**
     * @return mixed
     * @throws SessionException
     */
    public function refactorId() : self
    {
        $newSessionId  =   $this->generateId();
        if(!$this->isUniqueId($newSessionId)) {
            return $this->refactorId();
        }
        
        $currentId  =   $this->session->getId();
        $this->session->setId($newSessionId);

        $this->delete($currentId, true);
        $this->sessionCookie($newSessionId);

        return $this;
    }

    /**
     * Save session in storage
     */
    public function write()
    {
        try {
            // Make sure we have instance of ComelySession
            if(!$this->session instanceof ComelySession) {
                throw SessionException::sessionNotExists(__METHOD__);
            }

            // Prepare it for writing
            $this->session->encodeData($this->config->hashSalt, $this->config->hashCost);
            $payload    =   serialize($this->session);

            // Cipher Encryption?
            if($this->config->cipher instanceof Cipher) {
                $payload    =   $this->config->cipher->encrypt($payload);
            }

            // Write in storage
            $this->storage->write($this->session->getId(), $payload);
        } catch(\Throwable $t) {
            /**
             * Since this method would run at end of execution, its better to trigger an error alongside throwing
             * exception (assuming that is is being logged by error handler)
             */
            $methodString    =   method_exists($t, "getMethod") ? sprintf("%s: ", $t->getMethod()) :  "";
            trigger_error(
                $methodString . $t->getMessage(),
                E_USER_WARNING
            );

            // Re-throw
            throw $t;
        }
    }

    /**
     * Set COMELYSESSID cookie
     *
     * @param string $id
     * @return bool
     */
    private function sessionCookie(string $id) : bool
    {
        if($this->config->cookie    === true) {
            if(isset($_COOKIE["COMELYSESSID"])) {
                $_COOKIE["COMELYSESSID"]    =   $id;
            }

            return setcookie(
                "COMELYSESSID",
                $id,
                time() + $this->config->cookieLife,
                $this->config->cookiePath,
                $this->config->cookieDomain,
                $this->config->cookieSecure,
                $this->config->cookieHttpOnly
            );
        }

        return false;
    }

    /**
     * Generate a cryptographically strong session ID from using PHP7's random_bytes() CSPRNG
     *
     * @return string
     * @throws SessionException
     */
    private function generateId() : string
    {
        try {
            $sessionId  =   random_bytes(32);
            return bin2hex($sessionId);
        } catch(\Throwable $e) {
            throw new SessionException(__METHOD__, $e->getMessage());
        }
    }

    /**
     * Check if a session ID already exists
     *
     * @param string $sessionId
     * @return bool
     */
    private function isUniqueId(string $sessionId) : bool
    {
        try {
            return $this->storage->has($sessionId) ? false : true;
        } catch(ComelyException $e) {
            // Inspection failed for some reason, Its going to be a judgement call
            // Better return false?
        }

        return false;
    }

    /**
     * Deletes session from storage
     * No exception is thrown on failure
     *
     * @param string $sessionId
     * @param bool $triggerError
     */
    private function delete(string $sessionId, bool $triggerError = false)
    {
        try {
            $this->storage->delete($sessionId);
        } catch(ComelyException $e) {
             // Failed to delete session file
            if($triggerError) {
                trigger_error(sprintf('%s: %s', $e->getMethod(), $e->getMessage()), E_USER_WARNING);
            }
        }
    }

    /**
     * Returns 160-bit PBKDF2 hash in hexadecimal representation
     *
     * @param string $str
     * @param string $salt
     * @param int $cost
     * @return string
     */
    public static function saltedHash(string $str, string $salt, int $cost = 0) : string
    {
        return hash_pbkdf2("sha1", $str, $salt, $cost, 0, false);
    }

    /**
     * Configuration
     */

    /**
     * Sets salt for PBKDF2 payload hashing
     *
     * @param string $salt
     * @return Session
     */
    public function setHashSalt(string $salt) : self
    {
        $this->config->hashSalt =   $salt;
        return $this;
    }

    /**
     * Sets cost for PBKDF2 payload hashing
     *
     * @param int $cost
     * @return Session
     * @throws SessionException
     */
    public function setHashCost(int $cost) : self
    {
        if($cost    <=   0) {
            throw SessionException::configError("hashCost", "Cost must be a positive integer");
        }

        $this->config->hashCost =   $cost;
        return $this;
    }

    /**
     * Encrypt session payload with Cipher component
     *
     * @param Cipher $cipher
     * @return Session
     */
    public function useCipher(Cipher $cipher) : self
    {
        $this->config->cipher   =   $cipher;
        return $this;
    }

    /**
     * Set preference for COMELYSESSID cookie
     *
     * @param bool $set
     * @param int $life
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @throws SessionException
     */
    public function setCookie(
        bool $set = false,
        int $life = 0,
        string $path = "",
        string $domain = "",
        bool $secure = true,
        bool $httpOnly = true
    ) {
        if($life    <=   0) {
            throw SessionException::configError("cookieLife", "Expiry in seconds must be a positive integer");
        }

        $this->config->cookie   =   $set;
        $this->config->cookieLife   =   $life;
        $this->config->cookiePath   =   $path;
        $this->config->cookieDomain   =   $domain;
        $this->config->cookieSecure   =   $secure;
        $this->config->cookieHttpOnly   =   $httpOnly;
    }

    /**
     * Session will expire after given number of seconds of inactivity
     *
     * @param int $secs
     * @return Session
     * @throws SessionException
     */
    public function setSessionLife(int $secs) : self
    {
        if($secs    <=   0) {
            throw SessionException::configError("sessionLife", "Expiry in seconds must be a positive integer");
        }

        $this->config->sessionLife  =   $secs;
        return $this;
    }
}