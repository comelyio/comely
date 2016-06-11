<?php
declare(strict_types=1);

namespace Comely\Kernel\Http;

use Comely\Kernel\Exception\SessionException;
use Comely\Kernel\Http\Session\ComelySession;
use Comely\Kernel\Http\Session\Config;
use Comely\Kernel\Http\Session\ConfigTrait;
use Comely\Kernel\Repository;

use Comely\IO\Cache\CacheInterface;
use Comely\IO\Database\Database;
use Comely\IO\Filesystem\Disk;

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
            } elseif($this->storage instanceof Database) {
                // Read from database
                $session    =   $this->storage->table($this->config->dbTableName)->find("id=?", [$id])->fetchFirst();
                if(is_array($session)&& array_key_exists("payload", $session)) {
                    $session    =   $session["payload"];
                }
            } elseif($this->storage instanceof CacheInterface) {
                // Read from cache
            }
        } catch(\ComelyException $e) {
            // And error occurred while reading
        }

        // Expecting a String from read
        if(isset($session)  &&  is_string($session)) {
            // Un-serialize object
            $session    =   @unserialize($session, ["allowed_classes" => "Comely\\Kernel\\Http\\Session\\ComelySession"]);
            if($session instanceof ComelySession) {
                // Save ComelySession reference
                $this->session  =   $session;
            }
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
    private function save()
    {
        
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
            } elseif($this->storage instanceof Database) {
                $dbRow  =   $this->storage->table($this->config->dbTableName)->select("id")->find("id=?", $sessionId)->fetchFirst();
                return is_array($dbRow) ? false : true;
            } elseif($this->storage instanceof CacheInterface) {
                // Work is pending here
                return false;
            }
        } catch(\ComelyException $e) {
            // Inspection failed for some reason, Its going to be a judgement call
            // Better return false?
            return false;
        }
    }
}