<?php
/**
 * Created by javier
 * Date: 26/01/16
 * Time: 21:32
 */

namespace Linked\CookieAuth\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\Component\CookieComponent;
use Cake\Controller\Exception\MissingComponentException;
use Cake\Core\App;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Utility\Text;
use Linked\CookieAuth\Auth\Storage\StorageInterface;

/**
 * Class CookieAuthenticate
 * @package Linked\CookieAuth\Auth
 */
class CookieAuthenticate extends BaseAuthenticate
{
    /**
     * Default config for this object.
     *
     * - `fields` The fields to use to identify a user by.
     * - `userModel` The alias for users table, defaults to Users.
     * - `finder` The finder method to use to fetch user record. Defaults to 'all'.
     * - `passwordHasher` Password hasher class. Can be a string specifying class name
     *    or an array containing `className` key, any other keys will be passed as
     *    config to the class. Defaults to 'Default'.
     * - Options `scope` and `contain` have been deprecated since 3.1. Use custom
     *   finder instead to modify the query to fetch user record.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            'username' => 'username',
            'password' => 'password',
        ],
        'cookieFields' => [
            'identifier' => 'identifier',
            'token' => 'token',
            'tokenCreated' => null,
            'rememberMe' => 'remember_me',
        ],
        'userModel' => 'Users',
        'scope' => [],
        'finder' => 'all',
        'contain' => null,
        'passwordHasher' => 'Default',
        'cookie' => [
            'name' => 'RememberMe',
            'encryption' => 'aes',
            'expires' => '+1 week'
        ],
        'storage' => '\\Linked\\CookieAuth\\Auth\\Storage\\CacheStorage'
    ];

    /**
     * @var StorageInterface
     */
    private $_storage;

    /**
     * Authenticate a user based on the request information.
     *
     * @param \Cake\Network\Request $request Request to get authentication information from.
     * @param \Cake\Network\Response $response A response object that can have headers added.
     * @return mixed Either false on failure, or an array of user data on success.
     */
    public function authenticate(Request $request, Response $response)
    {
        return false;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getUser(Request $request)
    {
        $cookies = $this->_checkCookies();
        if (!$cookies) {
            return false;
        }

        $cookieFields = $this->_config['cookieFields'];
        $userData = $this->storage()->read($cookies[$cookieFields['identifier']]);

        if (!$userData || empty($userData['username'])) {
            return false;
        }
        if ($cookies[$cookieFields['token']] !== $userData['token']) {
            return false;
        }

        return $this->_findUser($userData['username']);
    }

    /**
     * @param Event $event
     * @param array $result
     * @param BaseAuthenticate $auth
     */
    public function afterIdentify(Event $event, array $result, BaseAuthenticate $auth)
    {
        /** @var Request $request */
        $request = $event->subject()->request;
        if (!$request->data($this->_config['cookieFields']['rememberMe'])) {
            return;
        }

        $_authConfig = $auth->config();
        $_username = $request->data($_authConfig['fields']['username']);

        $identifier = $this->_generateIdentifier($_username);
        $token = $this->_generateToken();
        $this->storage()->write($identifier, [
            'username' => $_username,
            'token' => $token,
        ]);

        // seteamos la cookie
        $cookieFields = $this->_config['cookieFields'];
        $this->_setCookies([
            $cookieFields['identifier'] => $identifier,
            $cookieFields['token'] => $token,
        ]);
    }

    /**
     * @param Event $event
     * @param array $user
     */
    public function logout(Event $event, array $user)
    {
        $cookies = $this->_checkCookies();
        if (!$cookies) {
            return;
        }

        $cookieFields = $this->_config['cookieFields'];
        $this->storage()->delete($cookies[$cookieFields['identifier']]);
        $this->_removeCookies();
    }

    /**
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Auth.afterIdentify' => 'afterIdentify',
            'Auth.logout' => 'logout'
        ];
    }

    /**
     * @param StorageInterface|null $storage
     * @return StorageInterface|null
     */
    public function storage(StorageInterface $storage = null)
    {
        if ($storage !== null) {
            $this->_storage = $storage;
            return null;
        }

        if ($this->_storage) {
            return $this->_storage;
        }

        $config = $this->_config['storage'];
        if (is_string($config)) {
            $class = $config;
            $config = [];
        } else {
            $class = $config['className'];
            unset($config['className']);
        }
        $className = App::className($class);
        if (!class_exists($className)) {
            throw new Exception(sprintf('CookieAuth storage adapter "%s" was not found.', $class));
        }
        $this->_storage = new $className($config);

        return $this->_storage;
    }

    /**
     * @return array|bool
     */
    private function _checkCookies()
    {
        $config = $this->_config['cookie'];
        if (!isset($this->_registry->Cookie) || !$this->_registry->Cookie instanceof CookieComponent) {
            throw new MissingComponentException(['class' => 'CookieComponent']);
        }

        $cookieName = $config['name'];
        unset($config['name']);
        $this->_registry->Cookie->configKey($cookieName, $config);
        $cookies = $this->_registry->Cookie->read($cookieName);

        $_fields = $this->_config['cookieFields'];
        if (empty($cookies) || empty($cookies[$_fields['identifier']]) || empty($cookies[$_fields['token']])) {
            return false;
        }
        return $cookies;
    }

    /**
     * @param array $value
     */
    private function _setCookies(array $value)
    {
        if (!isset($this->_registry->Cookie) || !$this->_registry->Cookie instanceof CookieComponent) {
            throw new MissingComponentException(['class' => 'CookieComponent']);
        }
        /** @var CookieComponent $cookie */
        $cookie = $this->_registry->Cookie;

        $config = $this->_config['cookie'];
        $cookieName = $config['name'];
        unset($config['name']);

        $cookie->configKey($cookieName, $config);
        $cookie->write($cookieName, $value);
    }

    /**
     * 
     */
    private function _removeCookies()
    {
        if (!isset($this->_registry->Cookie) || !$this->_registry->Cookie instanceof CookieComponent) {
            throw new MissingComponentException(['class' => 'CookieComponent']);
        }
        /** @var CookieComponent $cookie */
        $cookie = $this->_registry->Cookie;

        $config = $this->_config['cookie'];
        $cookieName = $config['name'];
        unset($config['name']);

        $cookie->configKey($cookieName, $config);
        $cookie->delete($cookieName);
    }

    /**
     * @param $_username
     * @return mixed
     */
    private function _generateIdentifier($_username)
    {
        return sha1($_username);
    }

    /**
     * @return string
     */
    private function _generateToken()
    {
        return sha1(Text::uuid());
    }
}