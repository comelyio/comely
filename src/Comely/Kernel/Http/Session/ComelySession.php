<?php
declare(strict_types=1);

namespace Comely\Kernel\Http\Session;
use Comely\Kernel\Exception\SessionException;
use Comely\Kernel\Http\Session;

/**
 * Class ComelySession
 * @package Comely\Kernel\Http\Session
 */
class ComelySession
{
    protected $id;
    public $data;
    protected $encoded;
    protected $hash;
    protected $timeStamp;

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
     * Encodes session data
     * For security reasons, session data should be encoded in JSON prior to serialize
     *
     * @param string $salt
     * @return string
     * @throws SessionException
     */
    public function encodeData(string $salt) : string
    {
        // Update timeStamp
        $this->timeStamp->last  =   microtime(true);

        // Make sure property data is in good shape
        if(!is_array($this->data)) {
            throw new SessionException(__METHOD__, 'Property "data" is corrupt', 1201);
        }

        // Encode JSON data
        $this->encoded =   json_encode($this->data);
        $this->hash =   Session::saltedHash($this->encoded, $salt);
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
        // Security feature, cross check preserved hash
        if(!hash_equals($this->dataHash, hash("sha1", serialize($this->data)))) {
            throw SessionException::readError("Hash mismatch");
        }



        // TODO: Check if its expired

    }
}