<?php
declare(strict_types=1);

namespace Comely\IO\Cache;

/**
 * Class Profile
 * @package Comely\IO\Cache
 */
class Profile
{
    /** @var string */
    public $type;
    /** @var string */
    public $data;
    /** @var string|null */
    public $instanceOf;

    /**
     * Profile constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->type =   gettype($data);
        if($this->type  === "object") {
            $this->instanceOf   =   get_class($data);
        }

        $this->data =   base64_encode(serialize($data));
    }

    /**
     * @throws CacheException
     */
    public function withdraw()
    {
        $data   =   unserialize(base64_decode($this->data));
        if(gettype($data)   !== $this->type) {
            throw new \DomainException(sprintf('Expecting "%1$s" got "%2$s"', $this->type, gettype($data)));
        }

        if($this->type  === "object") {
            if(!is_a($data, $this->instanceOf)) {
                throw new \DomainException(
                    sprintf(
                        'Expecting instance of "%1$s" got "%2$s"',
                        $this->instanceOf,
                        get_class($data)
                    )
                );
            }
        }

        return $data;
    }
}