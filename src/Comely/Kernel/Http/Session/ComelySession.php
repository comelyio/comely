<?php
declare(strict_types=1);

namespace Comely\Kernel\Http\Session;

/**
 * Class ComelySession
 * @package Comely\Kernel\Http\Session
 */
class ComelySession
{
    public $id;
    public $data;
    public $dataHash;
    public $timeStamp;

    /**
     * ComelySession constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id   =   $id;
        $this->data =   [];
        $this->dataHash =   "";
        $this->timeStamp    =   (object) ["create" => time(), "last" => time()];
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
     * Get session data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function calculateHash() : string
    {
        return hash("sha1", serialize($this->data));
    }

    /**
     * @return array
     */
    public function __sleep() : array
    {
        return ["id","data","dataHash","timeStamp"];
    }
}