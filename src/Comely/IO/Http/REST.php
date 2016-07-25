<?php
declare(strict_types=1);

namespace Comely\IO\Http;

use Comely\IO\Http\Exception\RestException;
use Comely\IO\Http\Request\Input;
use Comely\IO\Toolkit\Strings;

/**
 * Class REST
 * @package Comely\IO\Http
 */
class REST
{
    public static function parseRequest(callable $callback = null)
    {
        // HTTP Request Information
        $httpMethod =   $_SERVER["REQUEST_METHOD"] ?? "";
        $httpUri    =   $_SERVER["REQUEST_URI"] ?? "";
        $httpUri    =   explode("?", $httpUri)[0];

        // Create request
        $request    =   new Request(
            $httpMethod,
            $httpUri,
            new Input(self::getInputData($httpMethod), self::getHttpHeaders()),
            $callback
        );
        
        return $request;
    }

    /**
     * @param string $method
     * @return array
     * @throws RestException
     */
    public static function getInputData(string $method) : array
    {
        $inputData  =   [];
        $method =   strtoupper($method);

        // Get data in query
        if(isset($_SERVER["QUERY_STRING"])) {
            parse_str($_SERVER["QUERY_STRING"], $inputData);
        }

        // Content
        $contentType    =   $_SERVER["CONTENT_TYPE"] ?? "";
        $contentType    =   trim(explode(";", $contentType)[0]);

        $inputBody  =   null;
        $inputMerge =   null;

        if($method  === "POST") {
            $inputMerge =   $_POST;
        } elseif($method    === "PUT") {
            $inputBody  =   file_get_contents("php://input");
        } elseif($method    === "DELETE") {
            $inputBody  =   file_get_contents("php://input");
        }

        if(isset($inputBody)) {
            if($contentType === "application/json") {
                $inputMerge =   @json_decode($inputBody, true);
            } elseif($contentType   === "application/x-www-form-urlencoded") {
                $inputMerge = [];
                @parse_str($inputBody, $inputMerge);
            } elseif($contentType   === "multipart/form-data") {
                if($method  !== "POST") {
                    throw RestException::badInputContentMethod($method, $contentType);
                }
            } else {
                // Binary stream? Ignore
                $inputMerge =   [];
            }

            // Failed?
            if(!is_array($inputMerge)) {
                throw RestException::getInputDataFailed($method, $contentType);
            }
        }

        if(isset($inputMerge)) {
            $inputData  =   array_merge($inputData, $inputMerge);
        }

        return self::filterData($inputData);
    }

    /**
     * @param array $data
     * @return array
     */
    public static function filterData(array $data)
    {
        foreach($data as $key => $value) {
            if(is_string($value)) {
                $data[$key] =   Strings::filter($value, "adsq", true);
            } elseif(is_array($value)) {
                $data[$key] =   self::filterData($value);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getHttpHeaders() : array
    {
        $headers    =   [];
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5)   === "HTTP_") {
                $key    =   substr(strtolower($key), 5);
                $headers[$key]  =   $value;
            }
        }

        return $headers;
    }
}