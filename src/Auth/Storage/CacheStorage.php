<?php
/**
 * Created by javier
 * Date: 27/01/16
 * Time: 07:50
 */

namespace Linked\CookieAuth\Auth\Storage;

use Cake\Cache\Cache;
use Cake\Core\InstanceConfigTrait;

/**
 * Class CacheStorage
 * @package Linked\CookieAuth\Auth\Storage
 */
class CacheStorage implements StorageInterface
{
    use InstanceConfigTrait;

    /**
     * @var array
     */
    public $_defaultConfig = [
        'cacheConfig' => '_remember_me_',
        'className' => 'File',
        'prefix' => '_remember_me_',
        'path' => CACHE . 'remember_me/',
        'serialize' => true,
        'duration' => '+1 weeks',
    ];

    /**
     * CacheStorage constructor.
     * @param array $config initial configuration
     */
    public function __construct(array $config)
    {
        $this->config($config);
        $_config = $this->config();
        $_configName = $_config['cacheConfig'];
        unset($_config['cacheConfig']);
        Cache::config($_configName, $_config);
    }

    /**
     * @param string $identifier unique string identifier to store associated data
     * @return array Un array con la siguiente estructura
     *   [
     *     'username' => '...',
     *     'token' => '...'
     *   ]
     */
    public function read($identifier)
    {
        return Cache::read($identifier, $this->_config['cacheConfig']);
    }

    /**
     * @param string $identifier unique string identifier to store associated data
     * @param array $data Un array con la siguiente estructura
     *   [
     *     'username' => '...',
     *     'token' => '...'
     *   ]
     * @return bool
     */
    public function write($identifier, array $data)
    {
        return Cache::write($identifier, $data, $this->_config['cacheConfig']);
    }

    /**
     * @param string $identifier unique string identifier to store associated data
     * @return bool
     */
    public function delete($identifier)
    {
        return Cache::delete($identifier, $this->_config['cacheConfig']);
    }
}
