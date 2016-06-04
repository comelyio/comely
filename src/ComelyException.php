<?php
declare(strict_types=1);

/**
 * Class ComelyException
 * Base exception that all IO components extend
 */
class ComelyException extends \Exception
{
    protected $method;

    /**
     * ComelyException constructor.
     * @param string $method
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $method, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->method   =   $method;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @param string|null $pattern
     * @return string
     */
    public function getParsed(string $pattern = null) : string
    {
        if(empty($pattern)) {
            // Set default pattern
            $pattern    =   "{classMethod}: [#{code}] {message} in {basename(file)}:{line}";
        }

        return false;
    }
}