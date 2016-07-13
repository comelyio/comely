<?php
declare(strict_types=1);

namespace Comely\IO\Session;

/**
 * Class ComelySession
 * @package Comely\Kernel\Http\Session
 */
class ComelySession
{
    private $id;
    private $data;
    private $encoded;
    private $hash;
    private $timeStamp;

    /**
     * ComelySession constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id   =   $id;
        $this->data =   [];
        $this->encoded =   "";
        $this->hash =   "";
        $this->timeStamp    =   (object) [
            "create" => microtime(true),
            "last" => microtime(true)
        ];
    }

    /**
     * Get session ID
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get all session data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Set session data
     *
     * @param array $data
     * @return array
     */
    public function setData(array $data)
    {
        $this->data =   $data;
    }

    /**
     * Encodes session data
     * For security reasons, session data should be encoded in JSON prior to serialize
     *
     * @param string $salt
     * @param int $cost
     * @return string
     */
    public function encodeData(string $salt, int $cost) : string
    {
        // Update timeStamp
        $this->timeStamp->last  =   microtime(true);

        // Encode JSON data
        $this->encoded =   json_encode($this->data);
        $this->hash =   Session::saltedHash($this->encoded, $salt, $cost);
    }

    /**
     * Prepare session for writing
     *
     * @return array
     * @throws SessionException
     */
    public function __sleep() : array
    {
        return ["id","encoded","hash","timeStamp"];
    }

    /**
     * Prepare session for resuming
     */
    public function __wakeup()
    {
        // Check if all properties exist...
        foreach(["id","encoded","hash","timeStamp"] as $property) {
            if(!property_exists($this, $property)) {
                throw SessionException::badWakeUp();
            }
        }
    }

    /**
     * Prepares session after waking up
     *
     * @param int $expiry
     * @param string $salt
     * @param int $cost
     * @return bool
     * @throws SessionException
     */
    public function decodeData(int $expiry, string $salt, int $cost) : bool
    {
        // Check if this session needs decoding
        if(is_array($this->data)    ||  empty($this->encoded)) {
            throw new SessionException(__METHOD__, "Session is already decoded", 1502);
        }

        // Check validity
        $span   =   microtime(true) - $this->timeStamp->last;
        if($span    >=  $expiry) {
            // Session has expired
            return false;
        }

        // Checksum
        if(!hash_equals(Session::saltedHash($this->encoded, $salt, $cost), $this->hash)) {
            throw new SessionException(__METHOD__, "Session checksum failed", 1503);
        }

        // Decode data
        $this->data =   @json_decode($this->encoded, true);
        if(!is_array($this->data)) {
            throw new SessionException(__METHOD__, "Failed to decode JSON data", 1504);
        }

        // Release "encoded" property
        $this->encoded  =   null;

        // Successfully decoded
        return true;
    }
}