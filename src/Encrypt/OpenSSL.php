<?php

namespace Sparky7\Encrypt;

use Exception;

/**
 * OpenSSL wrapper class.
 */
class OpenSSL
{
    private $cipher;
    private $algo;

    private $key;
    private $iv;

    /**
     * Construct.
     *
     * @param string $cipher Cipher
     * @param string $algo   PBKDF2 algorythm
     */
    final public function __construct($cipher = 'AES-256-CBC', $algo = 'sha512')
    {
        $this->cipher = $cipher;
        $this->algo = $algo;
    }

    /**
     * Get Key length.
     *
     * @return int Key length
     */
    final private function keyLength()
    {
        switch ($this->cipher) {
            case 'AES-256-CBC':
            case 'AES-256-CFB':
                return 32;
            default:
                throw new Exception('Unkown key size');
        }
    }

    /**
     * Set Key.
     *
     * @param string $password   Password
     * @param string $salt       Salt
     * @param int    $iterations Iterations
     *
     * @return string key
     */
    final public function key($password, $salt, $iterations = 0)
    {
        $this->key = bin2hex(hash_pbkdf2($this->algo, $password, $salt, $iterations, $this->keyLength(), true));
    }

    /**
     * Get Key length.
     *
     * @return int Key length
     */
    final private function ivLength()
    {
        switch ($this->cipher) {
            case 'AES-256-CBC':
            case 'AES-256-CFB':
                return 16;
            default:
                throw new Exception('Unkown iv size');
        }
    }

    /**
     * Set IV.
     *
     * @param string $password   Password
     * @param string $salt       Salt
     * @param int    $iterations Iterations
     *
     * @return string key
     */
    final public function iv($password, $salt, $iterations = 0)
    {
        $this->iv = bin2hex(hash_pbkdf2($this->algo, $password, $salt, $iterations, $this->ivLength(), true));
    }

    /**
     * Encrypt string.
     *
     * @param string $string String to encrypt
     *
     * @return string Encrypted string
     */
    final public function encrypt($string)
    {
        return openssl_encrypt($string, $this->cipher, hex2bin($this->key), 0, hex2bin($this->iv));
    }

    /**
     * Decrypt string.
     *
     * @param string $string String to encrypt
     *
     * @return string Decrypted string
     */
    final public function decrypt($string)
    {
        $data = openssl_decrypt($string, $this->cipher, hex2bin($this->key), 0, hex2bin($this->iv));

        return (!openssl_error_string()) ? $data : null;
    }
}
