<?php
declare(strict_types=1);

namespace Comely\IO\Session;

use Comely\IO\Session\ComelySession\Proxy;
use ComelyException;
use Comely\IO\Cache\CacheInterface;
use Comely\IO\Database\Database;
use Comely\IO\Database\Schema;
use Comely\IO\Database\Schema\AbstractTable;
use Comely\IO\Filesystem\Disk;
use Comely\IO\Security\Cipher;

/**
 * Class Session
 * @package Comely\IO\Session
 */
class Session
{
    private $storage;
    private $session;
    private $config;

    use ConfigTrait;

    /**
     * Session constructor.
     * @param $storage
     * @throws SessionException;
     */
    public function __construct($storage)
    {
        // Determine Storage
        if($storage instanceof Disk) {
            // Storing session on Filesystem
            if("rw" !== $storage->diskPrivileges())  {
                // Filesystem\Disk instance must have read+write privileges
                throw SessionException::storageError(
                    "Filesystem\\Disk",
                    "Disk instance doesn't have read+write privileges"
                );
            }
        } elseif($storage instanceof Database) {
            // Storing session on Database
            Schema::loadTable($storage, "Comely\\IO\\Session\\Database\\Sessions");
            $storage    =   Schema::table("comely_sessions");
        } elseif($storage instanceof CacheInterface) {
            // Storing session on Cache engine
        } else {
            throw SessionException::badStorage();
        }

        // Set Storage
        $this->config   =   new Config();
        $this->storage  =   $storage;
    }

    /**
     * @param string|null $id
     * @return string
     * @throws SessionException
     */
    public function start(string $id = null) : string
    {
        // Check if session has already been started
        if($this->session instanceof Proxy) {
            throw SessionException::sessionAlreadyStarted();
        }

        // Read Session
        $sessionId  =   $id ?? $_COOKIE["COMELYSESSID"] ?? null;
        if(isset($sessionId)) {
            // Session ID found, attempt to read
            $this->resume($sessionId);
        }

        // Check if we got ComelySession from read
        if(!$this->session instanceof Proxy) {
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
     * @return Proxy
     */
    public function getSession() : Proxy
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
            if($this->storage instanceof Disk) {
                // Read session file from Disk
                $session    =   $this->storage->read($id . ".sess");
            } elseif($this->storage instanceof AbstractTable) {
                // Read from database
                $session    =   $this->storage->findById($id);
                if(is_array($session)   &&  array_key_exists("payload", $session)) {
                    $session    =   $session["payload"];
                }
            } elseif($this->storage instanceof CacheInterface) {
                // Read from cache
            }

            // Expecting a String from read
            if(!isset($session)||   !is_string($session)) {
                throw SessionException::readError("Failed to read session from storage");
            }

            // Cipher Encryption?
            if($this->config->cipher instanceof Cipher) {
                $session    =   $this->config->cipher->decrypt($session);
            }

            // Un-serialize object
            $session    =   @unserialize($session, [
                "allowed_classes"   =>    [
                    "Comely\\IO\\Session\\ComelySession",
                    "Comely\\IO\\Session\\ComelySession\\Bag",
                    "Comely\\IO\\Session\\ComelySession\\Proxy"
                ]
            ]);

            if($session instanceof Proxy) {
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
            return $this->create();
        }

        // Create new instance of ComelySession
        $this->session    =   new Proxy(new ComelySession($sessionId));
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
        
        $current    =   $this->session->getInstance();
        $this->session->setInstance(
            (new ComelySession($newSessionId))->setData($current->getData())
        );

        $this->delete($current->getId(), true);
        $this->sessionCookie($this->session->getId());

        return $this;
    }

    /**
     * Save session in storage
     */
    public function write()
    {
        try {
            // Make sure we have instance of ComelySession
            if(!$this->session instanceof Proxy) {
                throw SessionException::writeError('Cannot find instance of ComelySession for writing');
            }

            // Prepare it for writing
            $this->session->encodeData($this->config->hashSalt, $this->config->hashCost);
            $payload    =   serialize($this->session);

            // Cipher Encryption?
            if($this->config->cipher instanceof Cipher) {
                $payload    =   $this->config->cipher->encrypt($payload);
            }

            // Write in storage
            if($this->storage instanceof Disk) {
                // Write to Filesystem
                $this->storage->write(
                    $this->session->getId() . ".sess",
                    $payload,
                    Disk::WRITE_FLOCK
                );
            } elseif($this->storage instanceof AbstractTable) {
                // Write to Database
                $db =   $this->storage->getDb();
                $update =   $db->table($this->storage->getName())
                    ->find("id=:id", ["id" => $this->session->getId()])
                    ->update(
                        [
                            "payload"   =>  $payload,
                            "time_stamp"    =>  time()
                        ]
                    );

                // Make sure an exception is thrown even if DB instance is in silent mode
                if(!$update) {
                    throw SessionException::writeError(
                        $db->lastQuery->error ?? "Failed to write changes in database"
                    );
                }
            } elseif($this->storage instanceof CacheInterface) {
                // TODO: Write in cache
            }
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
            if($this->storage instanceof Disk) {
                return $this->storage->isReadable($sessionId . ".sess") ? false : true;
            } elseif($this->storage instanceof AbstractTable) {
                $db =   $this->storage->getDb();
                $row    =   $db->table($this->storage->getName())->select("id")->find("id=?", [$sessionId])->fetchFirst();
                return is_array($row) ? false : true;
            } elseif($this->storage instanceof CacheInterface) {
                // TODO: Implement cache
                return false;
            }
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
            if($this->storage instanceof Disk) {
                $this->storage->delete($sessionId . ".sess");
            } elseif($this->storage instanceof AbstractTable) {
                $db =   $this->storage->getDb();
                $delete    =   $db->table($this->storage->getName())->find("id=?", [$sessionId])->delete();

                // Throw an error even if database is in silent mode
                if(!$delete) {
                    throw new SessionException(
                        __METHOD__,
                        $db->lastQuery->error ?? "Failed to delete session from database",
                        1401
                    );
                }
            } elseif($this->storage instanceof CacheInterface) {
                // TODO: Implement cache
            }
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
}