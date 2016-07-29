<?php
declare(strict_types=1);

namespace Comely\IO\Security;

use Comely\IO\Security\Exception\CipherException;

/**
 * Class Cipher
 * This utility classes uses "OpenSSL" ext. to encrypt/decrypt subjects (scalar values, arrays or objects) as well as
 * saves preferred algorithm for PBKDF2 hashing.
 *
 * @package Comely\IO\Security
 */
class Cipher
{
    const PBKDF2_COST   =   64000;
    const KEY_SIZE  =   256;

    private $cipherMethod;
    private $defaultSecret;
    private $defaultHashAlgo;
    private $ivSize;

    /**
     * Cipher constructor.
     * @param string $cipherMethod
     * @throws CipherException
     */
    public function __construct(string $cipherMethod = "aes-256-cbc")
    {
        // Check requirements
        if(!extension_loaded("openssl")  ||  !function_exists("hash")) {
            throw CipherException::initError();
        }

        // Cipher Method
        if(!in_array($cipherMethod, openssl_get_cipher_methods())) {
            throw CipherException::badCipherMethod($cipherMethod);
        }

        // Bootstrap
        $this->cipherMethod =   $cipherMethod;
        $this->defaultSecret    =   $this->validateKey(self::createKey("comely", 1), __METHOD__);
        $this->defaultHashAlgo  =   $this->defaultHashAlgo("sha1");
        $this->ivSize   =   openssl_cipher_iv_length($this->cipherMethod);
    }

    /**
     * Sets default secret
     *
     * @param string $secret
     * @return Cipher
     */
    public function defaultSecret(string $secret) : self
    {
        $this->defaultSecret    =   $this->validateKey($secret, __METHOD__);
        return $this;
    }

    /**
     * Sets default hash algorithm
     *
     * @param string $algo
     * @return Cipher
     * @throws CipherException
     */
    public function defaultHashAlgo(string $algo) : self
    {
        // Check if its a valid hashing algorithm
        if(!in_array($algo, hash_algos())) {
            throw CipherException::badHashAlgo($algo);
        }

        $this->defaultHashAlgo  =   $algo;
        return $this;
    }

    /**
     * Encryption
     * This method creates and encrypts indexed array having data type of $subject parameter in first index and
     * actual $subject value in second. If subject is an array or an object, this method will save $subject after
     * serializing and encoding in base64
     *
     * Resources cannot be encrypted
     *
     * @param $subject
     * @param string|null $secret
     * @return string
     * @throws CipherException
     */
    public function encrypt($subject, string $secret = null) : string
    {
        $secret =   (!empty($secret)) ? $this->validateKey($secret, __METHOD__) : $this->defaultSecret;
        $encrypt =   [gettype($subject), $subject];

        // Check if its encrypt-able
        if(!in_array($encrypt[0], ["boolean","integer","double","string","array","object"])) {
            throw CipherException::badDataType($encrypt[0]);
        }

        // Serialize Array and Objects
        if(in_array($encrypt[0], ["array","object"])) {
            $encrypt[1]  =   base64_encode(serialize($encrypt[1]));
        }

        // Get encrypted String
        $iv =   random_bytes($this->ivSize);
        $encrypted  =   openssl_encrypt(
            json_encode($encrypt),
            $this->cipherMethod,
            $secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Ensure successful encryption
        if(!is_string($encrypted)) {
            throw new CipherException(__METHOD__, "Encryption failed", 1201);
        }

        // Return encrypted String joint with IV
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt an encrypted subject back to its initial form
     *
     * @param string $encrypted
     * @param string|null $secret
     * @return mixed
     * @throws CipherException
     */
    public function decrypt(string $encrypted, string $secret = null)
    {
        $secret =   (!empty($secret)) ? $this->validateKey($secret, __METHOD__) : $this->defaultSecret;

        // Convert encrypted String back to binary and split IV
        $encrypted  =   base64_decode($encrypted);
        $iv =   substr($encrypted, 0, $this->ivSize);
        $encrypted  =   substr($encrypted, $this->ivSize);

        // Decrypt
        $decrypted  =   openssl_decrypt(
            $encrypted,
            $this->cipherMethod,
            $secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Ensure successful decryption
        if(!is_string($decrypted)) {
            throw new CipherException(__METHOD__, "Decryption failed", 1202);
        }

        // Put encrypted subject back in its actual form
        $decrypted  =   @json_decode(trim($decrypted), true);
        if(!is_array($decrypted) || !array_key_exists(0, $decrypted) || !array_key_exists(1, $decrypted)) {
            // Looks like this key wasn't encrypted with encrypt method of this class
            throw new CipherException(__METHOD__, "Encountered corrupt data after decryption", 1203);
        }

        // Retrieve subject
        if(in_array($decrypted[0], ["array", "object"])) {
            // Array or objects must be un-serialized
            $subject    =   @unserialize(base64_decode($decrypted[1]));
        } else {
            // Probably some scalar value
            $subject    =   $decrypted[1];
        }

        // Ensure that subject is back in its initial form
        if(gettype($subject)    !== $decrypted[0]) {
            throw new CipherException(
                __METHOD__,
                sprintf(
                    'Expected data type "%1$s" after decryption, got "%2$s"',
                    $decrypted[0],
                    gettype($subject)
                ),
                1204
            );
        }

        // Return decrypted subject
        return $subject;
    }

    /**
     * Generates a PBKDF2 hash
     *
     * @param string $data
     * @param string $salt
     * @param int $cost
     * @param string|null $algo
     * @return string
     * @throws CipherException
     */
    public function hash(string $data, string $salt = "", int $cost = self::PBKDF2_COST, string $algo = null) : string
    {
        $algo   =   (!empty($algo)) ? $algo : $this->defaultHashAlgo;
        $salt   =   (!empty($salt)) ? $salt : $this->defaultSecret;
        if(!in_array($algo, hash_algos())) {
            throw CipherException::badHashAlgo($algo);
        }

        return hash_pbkdf2($algo, $data, $salt, $cost, 0, false);
    }

    /**
     * Checks if $key is self::KEY_SIZE bits long hexadecimal string
     *
     * @param string $key
     * @param string $method
     * @return string
     * @throws CipherException
     */
    private function validateKey(string $key, string $method) : string
    {
        if(!ctype_xdigit($key) || strlen(hex2bin($key))*8  !== self::KEY_SIZE) {
            throw CipherException::badKey($method, self::KEY_SIZE);
        }

        return hex2bin($key);
    }

    /**
     * Create key using PBKDF2
     * This method turns a random variable length string into compatible key. This is not really a secure option.
     * Cipher keys should be generated and saved using Cipher::randomKey() method.
     *
     * @param string $words
     * @param int $cost
     * @return string
     */
    public static function createKey(string $words, int $cost = self::PBKDF2_COST) : string
    {
        return bin2hex(
            hash_pbkdf2("sha512", $words, strrev($words), $cost, (self::KEY_SIZE/8), true)
        );
    }

    /**
     * Return required key size for cipher
     *
     * @return int
     */
    public static function keySize() : int
    {
        return self::KEY_SIZE;
    }
}