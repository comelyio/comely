<?php
declare(strict_types=1);

namespace Comely\Kernel\Http;

use ComelyException;
use Comely\IO\Cache\CacheInterface;
use Comely\IO\Database\Database;
use Comely\IO\Database\Schema\AbstractTable;
use Comely\IO\Database\Schema;
use Comely\IO\Filesystem\Disk;

use Comely\Kernel\Exception\SessionException;
use Comely\Kernel\Http\Session\ComelySession;
use Comely\Kernel\Http\Session\Config;
use Comely\Kernel\Http\Session\ConfigTrait;
use Comely\Kernel\Repository;


/**
 * Class Session
 * @package Comely\IO\Session
 */
class Session extends Repository
{
    private $storage;
    private $session;
    private $config;

    use ConfigTrait;

    /**
     * Session constructor.
     * @param $storage
     * @param string|null $id
     * @throws SessionException;
     */
    public function __construct($storage, string $id = null)
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
            Schema::loadTable($storage, "Comely\\Kernel\\Http\\Session\\Database\\Sessions");
            $storage    =   Schema::table("comely_sessions");
        } elseif($storage instanceof CacheInterface) {
            // Storing session on Cache engine
        } else {
            throw SessionException::badStorage();
        }

        // Set Storage
        $this->storage  =   $storage;

        // Setup Session Configuration
        $this->config   =   new Config();

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
                $session   =   $this->storage->read($id . ".sess");
            } elseif($this->storage instanceof AbstractTable) {
                // Read from database
                $session    =   $this->storage->findById($id);
                if(is_array($session)&& array_key_exists("payload", $session)) {
                    $session    =   $session["payload"];
                }
            } elseif($this->storage instanceof CacheInterface) {
                // Read from cache
            }


            // Expecting a String from read
            if(isset($session)  &&  is_string($session)) {
                try {
                    // Un-serialize object
                    $session    =   unserialize($session, [
                        "allowed_classes"   =>    [
                            "Comely\\Kernel\\Http\\Session\\ComelySession"
                        ]
                    ]);

                    if($session instanceof ComelySession) {
                        // Save ComelySession reference
                        $this->session  =   $session;
                    }
                } catch(\Throwable $e) {
                    // An error occurred while waking up
                    // Do nothing...
                }
            } else {
                throw SessionException::readError("Failed to read session from storage");
            }
        } catch(ComelyException $e) {
            // And error occurred while reading
            // Do nothing...
        }
    }

    /**
     * Create a ComelySession
     *
     * @return bool
     * @throws SessionException
     */
    private function create() : bool
    {
        // Create secure session ID
        $sessionId  =   $this->generateId();
        if(!$this->isUniqueId($sessionId)) {
            return $this->create();
        }

        // Create new instance of ComelySession
        $session    =   new ComelySession($sessionId);
    }

    /**
     * Save session in storage
     */
    private function write()
    {
        // Make sure we have instance of ComelySession
        if(!$this->session instanceof ComelySession) {
            throw SessionException::writeError('Cannot find instance of ComelySession for writing');
        }
        
        // Prepare it for writing
        $this->session->encodeData($this->config->hashSalt);
        $payload    =   serialize($this->session);

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
                $row    =   $db->table($this->storage->getName())->select("id")->find("id=?", $sessionId)->fetchFirst();
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
     * Returns SHA1 salted hash
     *
     * @param string $str
     * @param string $salt
     * @return string
     */
    public static function saltedHash(string $str, string $salt) : string
    {
        // Return a SHA1 salted hash
        return hash("sha1", sprintf("%s*%s", $str, $salt));
    }
}