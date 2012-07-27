<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     1.6.0
 * @package     Slim
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Session Cookie
 *
 * This class provides an HTTP cookie storage mechanism
 * for session data. This class avoids using a PHP session
 * and instead serializes/unserializes the $_SESSION global
 * variable to/from an HTTP cookie.
 *
 * If a secret key is provided with this middleware, the HTTP
 * cookie will be checked for integrity to ensure the client-side
 * cookie is not changed.
 *
 * You should NEVER store sensitive data in a client-side cookie
 * in any format, encrypted or not. If you need to store sensitive
 * user information in a session, you should rely on PHP's native
 * session implementation, or use other middleware to store
 * session data in a database or alternative server-side cache.
 *
 * Because this class stores serialized session data in an HTTP cookie,
 * you are inherently limtied to 4 Kb. If you attempt to store
 * more than this amount, serialization will fail.
 *
 * @package     Slim
 * @author     Josh Lockhart
 * @since      1.5.2
 */
class Slim_Middleware_SessionCookie extends Slim_Middleware {
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param   array $settings
     * @return  void
     */
    public function __construct( $settings = array() ) {
        $this->settings = array_merge(array(
            'expires' => '20 minutes',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'name' => 'slim_session',
            'secret' => 'CHANGE_ME',
            'cipher' => MCRYPT_RIJNDAEL_256,
            'cipher_mode' => MCRYPT_MODE_CBC
        ), $settings);
        if ( is_string($this->settings['expires']) ) {
            $this->settings['expires'] = strtotime($this->settings['expires']);
        }
    }

    /**
     * Call
     * @return void
     */
    public function call() {
        $this->loadSession();
        $this->next->call();
        $this->saveSession();
    }

    /**
     * Load session
     * @param   array $env
     * @return  void
     */
    protected function loadSession() {
        session_start();
        $value = Slim_Http_Util::decodeSecureCookie(
            $this->app->request()->cookies($this->settings['name']),
            $this->settings['secret'],
            $this->settings['cipher'],
            $this->settings['cipher_mode']
        );
        if ( $value ) {
            $_SESSION = unserialize($value);
        } else {
            $_SESSION = array();
        }
    }

    /**
     * Save session
     * @return  void
     */
    protected function saveSession() {
        $value = Slim_Http_Util::encodeSecureCookie(
            serialize($_SESSION),
            $this->settings['expires'],
            $this->settings['secret'],
            $this->settings['cipher'],
            $this->settings['cipher_mode']
        );
        if ( strlen($value) > 4096 ) {
            $this->app->getLog()->error('WARNING! Slim_Middleware_SessionCookie data size is larger than 4KB. Content save failed.');
        } else {
            $this->app->response()->setCookie($this->settings['name'], array(
                'value' => $value,
                'domain' => $this->settings['domain'],
                'path' => $this->settings['path'],
                'expires' => $this->settings['expires'],
                'secure' => $this->settings['secure'],
                'httponly' => $this->settings['httponly']
            ));
        }
        session_destroy();
    }
}