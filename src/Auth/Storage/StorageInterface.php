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
     * Set or get the configuration for this storage
     *
     * @param null|string|array $key config key or array to set, null to get the config
     * @param null|string $value when $key is a string, this is the value to set for that key.
     * @param bool $merge if merge with actual config or override it.
     * @return mixed
     */
    public function config($key = null, $value = null, $merge = true);
    
    /**
     * @param string $identifier unique string identifier to store associated data
     * @return array Un array con la siguiente estructura
     *   [
     *     'username' => '...',
     *     'token' => '...'
     *   ]
     */
    public function read($identifier);

    /**
     * @param string $identifier unique string identifier to store associated data
     * @param array $data Un array con la siguiente estructura
     *   [
     *     'username' => '...',
     *     'token' => '...'
     *   ]
     * @return bool
     */
    public function write($identifier, array $data);

    /**
     * @param string $identifier unique string identifier to store associated data
     * @return bool
     */
    public function delete($identifier);
}
