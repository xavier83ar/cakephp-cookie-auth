<?php
/**
 * Created by javier
 * Date: 27/01/16
 * Time: 07:51
 */

namespace Linked\CookieAuth\Auth\Storage;

/**
 * Interface StorageInterface
 * @package Linked\CookieAuth\Auth\Storage
 */
interface StorageInterface
{
    /**
     * @param null $key
     * @param null $value
     * @param bool $merge
     * @return mixed
     */
    public function config($key = null, $value = null, $merge = true);
    
    /**
     * @param $identifier string
     * @return array Un array con la siguiente estructura
     *   [
     *     'username' => '...',
     *     'token' => '...'
     *   ]
     */
    public function read($identifier);

    /**
     * @param $identifier
     * @param array $data
     * @return boolean
     */
    public function write($identifier, array $data);

    /**
     * @param $identifier
     * @return boolean
     */
    public function delete($identifier);
}