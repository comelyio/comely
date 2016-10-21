<?php
declare(strict_types=1);

namespace Comely\IO\Http\Request;

use Comely\IO\Http\Exception\RequestException;
use Comely\IO\Http\Request;

/**
 * Class Response
 * @package Comely\IO\Http\Request
 */
class Response implements \Countable
{
    /** @var int */
    private $count;
    /** @var array */
    private $data;
    /** @var null|string */
    private $format;
    /** @var Request */
    private $request;

    /**
     * Response constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->count    =   0;
        $this->data =   [];
        $this->request  =   $request;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->count;
    }

    /**
     * @param int $httpResponseCode
     * @return Response
     */
    public function setCode(int $httpResponseCode = 200) : self
    {
        http_response_code($httpResponseCode);
        return $this;
    }

    /**
     * @param string $format
     * @return Response
     */
    public function setFormat(string $format) : self
    {
        $this->format   =   str_replace(["text/","application/"], "", strtolower($format));
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Response
     * @throws RequestException
     */
    public function sendHeader(string $name, string $value) : self
    {
        if(strtolower($name)    === "location") {
            // Suggest using "redirect" method
            throw RequestException::sendHeaderLocation(__METHOD__);
        }

        // Send raw header
        header(sprintf('%1$s: %2$s', $name, $value));
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return Response
     * @throws RequestException
     */
    public function set(string $key, $value) : self
    {
        if(is_object($value)) {
            // Convert object to array
            $value  =   json_decode(json_encode($value), true);
        }

        if(!is_scalar($value)   &&  !is_array($value)   &&  !is_null($value)) {
            throw RequestException::setBadData(__METHOD__, $key, gettype($value));
        }

        $this->count++;
        $this->data[$key]   =   $value;
        return $this;
    }

    /**
     * @param string $key
     * @return Response
     */
    public function delete(string $key) : self
    {
        $this->count--;
        unset($this->data[$key]);
        return $this;
    }

    /**
     * @param string $to
     */
    public function redirect(string $to)
    {
        header("Location: " . $to);
        exit();
    }

    /**
     * @throws RequestException
     */
    public function send()
    {
        if($this->format    === "json") {
            // Json
            $this->sendHeader("Content-type", "application/json; charset=utf-8");
            print json_encode($this->data);
            return; // Return
        } elseif($this->format  === "jsonp") {
            // Jsonp
            $this->sendHeader("Content-type", "application/json; charset=utf-8");
            $params =   $this->request->getInput()->getData();
            $jsonpFunction  =   $params["_callback"] ?? "callback";
            printf("%s(%s);", $jsonpFunction, json_encode($this->data));
            return; // Return
        } elseif($this->format  === "javascript") {
            $this->sendHeader("Content-type", "application/javascript; charset=utf-8");
        }

        // If data has only 1 key, get that for body, otherwise get print_r of entire data array
        reset($this->data);
        $body   =   count($this->data)  === 1 ? $this->data[key($this->data)] : print_r($this->data, true);

        // Send body
        print $body;
        return; // Return
    }
}