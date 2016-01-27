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
     * @param array $config
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
     * @param $identifier string
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
     * @param $identifier
     * @param array $data
     * @return boolean
     */
    public function write($identifier, array $data)
    {
        return Cache::write($identifier, $data, $this->_config['cacheConfig']);
    }

    /**
     * @param $identifier
     * @return boolean
     */
    public function delete($identifier)
    {
        return Cache::delete($identifier, $this->_config['cacheConfig']);
    }
}