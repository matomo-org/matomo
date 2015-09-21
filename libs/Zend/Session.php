<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Session.php 24196 2011-07-05 15:58:11Z matthew $
 * @since      Preview Release 0.2
 */


/**
 * @see Zend_Session_Abstract
 */
// require_once 'Zend/Session/Abstract.php';

/**
 * @see Zend_Session_Namespace
 */
// require_once 'Zend/Session/Namespace.php';

/**
 * @see Zend_Session_SaveHandler_Interface
 */
// require_once 'Zend/Session/SaveHandler/Interface.php';


/**
 * Zend_Session
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session extends Zend_Session_Abstract
{
    /**
     * Whether or not Zend_Session is being used with unit tests
     *
     * @internal
     * @var bool
     */
    public static $_unitTestEnabled = false;

    /**
     * $_throwStartupException
     *
     * @var bool|bitset This could also be a combiniation of error codes to catch
     */
    protected static $_throwStartupExceptions = true;

    /**
     * Check whether or not the session was started
     *
     * @var bool
     */
    private static $_sessionStarted = false;

    /**
     * Whether or not the session id has been regenerated this request.
     *
     * Id regeneration state
     * <0 - regenerate requested when session is started
     * 0  - do nothing
     * >0 - already called session_regenerate_id()
     *
     * @var int
     */
    private static $_regenerateIdState = 0;

    /**
     * Private list of php's ini values for ext/session
     * null values will default to the php.ini value, otherwise
     * the value below will overwrite the default ini value, unless
     * the user has set an option explicity with setOptions()
     *
     * @var array
     */
    private static $_defaultOptions = array(
        'save_path'                 => null,
        'name'                      => null, /* this should be set to a unique value for each application */
        'save_handler'              => null,
        //'auto_start'                => null, /* intentionally excluded (see manual) */
        'gc_probability'            => null,
        'gc_divisor'                => null,
        'gc_maxlifetime'            => null,
        'serialize_handler'         => null,
        'cookie_lifetime'           => null,
        'cookie_path'               => null,
        'cookie_domain'             => null,
        'cookie_secure'             => null,
        'cookie_httponly'           => null,
        'use_cookies'               => null,
        'use_only_cookies'          => 'on',
        'referer_check'             => null,
        'entropy_file'              => null,
        'entropy_length'            => null,
        'cache_limiter'             => null,
        'cache_expire'              => null,
        'use_trans_sid'             => null,
        'bug_compat_42'             => null,
        'bug_compat_warn'           => null,
        'hash_function'             => null,
        'hash_bits_per_character'   => null
    );

    /**
     * List of options pertaining to Zend_Session that can be set by developers
     * using Zend_Session::setOptions(). This list intentionally duplicates
     * the individual declaration of static "class" variables by the same names.
     *
     * @var array
     */
    private static $_localOptions = array(
        'strict'                => '_strict',
        'remember_me_seconds'   => '_rememberMeSeconds',
        'throw_startup_exceptions' => '_throwStartupExceptions'
    );

    /**
     * Whether or not write close has been performed.
     *
     * @var bool
     */
    private static $_writeClosed = false;

    /**
     * Whether or not session id cookie has been deleted
     *
     * @var bool
     */
    private static $_sessionCookieDeleted = false;

    /**
     * Whether or not session has been destroyed via session_destroy()
     *
     * @var bool
     */
    private static $_destroyed = false;

    /**
     * Whether or not session must be initiated before usage
     *
     * @var bool
     */
    private static $_strict = false;

    /**
     * Default number of seconds the session will be remembered for when asked to be remembered
     *
     * @var int
     */
    private static $_rememberMeSeconds = 1209600; // 2 weeks

    /**
     * Whether the default options listed in Zend_Session::$_localOptions have been set
     *
     * @var bool
     */
    private static $_defaultOptionsSet = false;

    /**
     * A reference to the set session save handler
     *
     * @var Zend_Session_SaveHandler_Interface
     */
    private static $_saveHandler = null;


    /**
     * Constructor overriding - make sure that a developer cannot instantiate
     */
    protected function __construct()
    {
    }


    /**
     * setOptions - set both the class specified
     *
     * @param  array $userOptions - pass-by-keyword style array of <option name, option value> pairs
     * @throws Zend_Session_Exception
     * @return void
     */
    public static function setOptions(array $userOptions = array())
    {
        // set default options on first run only (before applying user settings)
        if (!self::$_defaultOptionsSet) {
            foreach (self::$_defaultOptions as $defaultOptionName => $defaultOptionValue) {
                if (isset(self::$_defaultOptions[$defaultOptionName])) {
                    @ini_set("session.$defaultOptionName", $defaultOptionValue);
                }
            }

            self::$_defaultOptionsSet = true;
        }

        // set the options the user has requested to set
        foreach ($userOptions as $userOptionName => $userOptionValue) {

            $userOptionName = strtolower($userOptionName);

            // set the ini based values
            if (array_key_exists($userOptionName, self::$_defaultOptions)) {
                @ini_set("session.$userOptionName", $userOptionValue);
            }
            elseif (isset(self::$_localOptions[$userOptionName])) {
                self::${self::$_localOptions[$userOptionName]} = $userOptionValue;
            }
            else {
                /** @see Zend_Session_Exception */
                // require_once 'Zend/Session/Exception.php';
                throw new Zend_Session_Exception("Unknown option: $userOptionName = $userOptionValue");
            }
        }
    }

    /**
     * getOptions()
     *
     * @param string $optionName OPTIONAL
     * @return array|string
     */
    public static function getOptions($optionName = null)
    {
        $options = array();
        foreach (ini_get_all('session') as $sysOptionName => $sysOptionValues) {
            $options[substr($sysOptionName, 8)] = $sysOptionValues['local_value'];
        }
        foreach (self::$_localOptions as $localOptionName => $localOptionMemberName) {
            $options[$localOptionName] = self::${$localOptionMemberName};
        }

        if ($optionName) {
            if (array_key_exists($optionName, $options)) {
                return $options[$optionName];
            }
            return null;
        }

        return $options;
    }

    /**
     * setSaveHandler() - Session Save Handler assignment
     *
     * @param Zend_Session_SaveHandler_Interface $interface
     * @return void
     */
    public static function setSaveHandler(Zend_Session_SaveHandler_Interface $saveHandler)
    {
        self::$_saveHandler = $saveHandler;

        if (self::$_unitTestEnabled) {
            return;
        }

        session_set_save_handler(
            array(&$saveHandler, 'open'),
            array(&$saveHandler, 'close'),
            array(&$saveHandler, 'read'),
            array(&$saveHandler, 'write'),
            array(&$saveHandler, 'destroy'),
            array(&$saveHandler, 'gc')
            );
    }


    /**
     * getSaveHandler() - Get the session Save Handler
     *
     * @return Zend_Session_SaveHandler_Interface
     */
    public static function getSaveHandler()
    {
        return self::$_saveHandler;
    }


    /**
     * regenerateId() - Regenerate the session id.  Best practice is to call this after
     * session is started.  If called prior to session starting, session id will be regenerated
     * at start time.
     *
     * @throws Zend_Session_Exception
     * @return void
     */
    public static function regenerateId()
    {
        if (!self::$_unitTestEnabled && headers_sent($filename, $linenum)) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception("You must call " . __CLASS__ . '::' . __FUNCTION__ .
                "() before any output has been sent to the browser; output started in {$filename}/{$linenum}");
        }

        if ( !self::$_sessionStarted ) {
            self::$_regenerateIdState = -1;
        } else {
            if (!self::$_unitTestEnabled) {
                session_regenerate_id(true);
            }
            self::$_regenerateIdState = 1;
        }
    }


    /**
     * rememberMe() - Write a persistent cookie that expires after a number of seconds in the future. If no number of
     * seconds is specified, then this defaults to self::$_rememberMeSeconds.  Due to clock errors on end users' systems,
     * large values are recommended to avoid undesirable expiration of session cookies.
     *
     * @param int $seconds OPTIONAL specifies TTL for cookie in seconds from present time
     * @return void
     */
    public static function rememberMe($seconds = null)
    {
        $seconds = (int) $seconds;
        $seconds = ($seconds > 0) ? $seconds : self::$_rememberMeSeconds;

        self::rememberUntil($seconds);
    }


    /**
     * forgetMe() - Write a volatile session cookie, removing any persistent cookie that may have existed. The session
     * would end upon, for example, termination of a web browser program.
     *
     * @return void
     */
    public static function forgetMe()
    {
        self::rememberUntil(0);
    }


    /**
     * rememberUntil() - This method does the work of changing the state of the session cookie and making
     * sure that it gets resent to the browser via regenerateId()
     *
     * @param int $seconds
     * @return void
     */
    public static function rememberUntil($seconds = 0)
    {
        if (self::$_unitTestEnabled) {
            self::regenerateId();
            return;
        }

        $cookieParams = session_get_cookie_params();

        session_set_cookie_params(
            $seconds,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure']
            );

        // normally "rememberMe()" represents a security context change, so should use new session id
        self::regenerateId();
    }


    /**
     * sessionExists() - whether or not a session exists for the current request
     *
     * @return bool
     */
    public static function sessionExists()
    {
        if (ini_get('session.use_cookies') == '1' && isset($_COOKIE[session_name()])) {
            return true;
        } elseif (!empty($_REQUEST[session_name()])) {
            return true;
        } elseif (self::$_unitTestEnabled) {
            return true;
        }

        return false;
    }


    /**
     * Whether or not session has been destroyed via session_destroy()
     *
     * @return bool
     */
    public static function isDestroyed()
    {
        return self::$_destroyed;
    }


    /**
     * start() - Start the session.
     *
     * @param bool|array $options  OPTIONAL Either user supplied options, or flag indicating if start initiated automatically
     * @throws Zend_Session_Exception
     * @return void
     */
    public static function start($options = false)
    {
        if (self::$_sessionStarted && self::$_destroyed) {
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception('The session was explicitly destroyed during this request, attempting to re-start is not allowed.');
        }

        if (self::$_sessionStarted) {
            return; // already started
        }

        // make sure our default options (at the least) have been set
        if (!self::$_defaultOptionsSet) {
            self::setOptions(is_array($options) ? $options : array());
        }

        // In strict mode, do not allow auto-starting Zend_Session, such as via "new Zend_Session_Namespace()"
        if (self::$_strict && $options === true) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception('You must explicitly start the session with Zend_Session::start() when session options are set to strict.');
        }

        $filename = $linenum = null;
        if (!self::$_unitTestEnabled && headers_sent($filename, $linenum)) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception("Session must be started before any output has been sent to the browser;"
               . " output started in {$filename}/{$linenum}");
        }

        // See http://www.php.net/manual/en/ref.session.php for explanation
        if (!self::$_unitTestEnabled && defined('SID')) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception('session has already been started by session.auto-start or session_start()');
        }

        /**
         * Hack to throw exceptions on start instead of php errors
         * @see http://framework.zend.com/issues/browse/ZF-1325
         */

        $errorLevel = (is_int(self::$_throwStartupExceptions)) ? self::$_throwStartupExceptions : E_ALL;

        /** @see Zend_Session_Exception */
        if (!self::$_unitTestEnabled) {

            if (self::$_throwStartupExceptions) {
                // require_once 'Zend/Session/Exception.php';
                set_error_handler(array('Zend_Session_Exception', 'handleSessionStartError'), $errorLevel);
            }

            $startedCleanly = session_start();

            if (self::$_throwStartupExceptions) {
                restore_error_handler();
            }

            if (!$startedCleanly || Zend_Session_Exception::$sessionStartError != null) {
                if (self::$_throwStartupExceptions) {
                    set_error_handler(array('Zend_Session_Exception', 'handleSilentWriteClose'), $errorLevel);
                }
                session_write_close();
                if (self::$_throwStartupExceptions) {
                    restore_error_handler();
                    throw new Zend_Session_Exception(__CLASS__ . '::' . __FUNCTION__ . '() - ' . Zend_Session_Exception::$sessionStartError);
                }
            }
        }

        parent::$_readable = true;
        parent::$_writable = true;
        self::$_sessionStarted = true;
        if (self::$_regenerateIdState === -1) {
            self::regenerateId();
        }

        // run validators if they exist
        if (isset($_SESSION['__ZF']['VALID'])) {
            self::_processValidators();
        }

        self::_processStartupMetadataGlobal();
    }


    /**
     * _processGlobalMetadata() - this method initizes the sessions GLOBAL
     * metadata, mostly global data expiration calculations.
     *
     * @return void
     */
    private static function _processStartupMetadataGlobal()
    {
        // process global metadata
        if (isset($_SESSION['__ZF'])) {

            // expire globally expired values
            foreach ($_SESSION['__ZF'] as $namespace => $namespace_metadata) {

                // Expire Namespace by Time (ENT)
                if (isset($namespace_metadata['ENT']) && ($namespace_metadata['ENT'] > 0) && (time() > $namespace_metadata['ENT']) ) {
                    unset($_SESSION[$namespace]);
                    unset($_SESSION['__ZF'][$namespace]);
                }

                // Expire Namespace by Global Hop (ENGH) if it wasnt expired above
                if (isset($_SESSION['__ZF'][$namespace]) && isset($namespace_metadata['ENGH']) && $namespace_metadata['ENGH'] >= 1) {

                    $_SESSION['__ZF'][$namespace]['ENGH']--;

                    if ($_SESSION['__ZF'][$namespace]['ENGH'] === 0) {
                        if (isset($_SESSION[$namespace])) {
                            parent::$_expiringData[$namespace] = $_SESSION[$namespace];
                            unset($_SESSION[$namespace]);
                        }
                        unset($_SESSION['__ZF'][$namespace]);
                    }
                }

                // Expire Namespace Variables by Time (ENVT)
                if (isset($namespace_metadata['ENVT'])) {
                    foreach ($namespace_metadata['ENVT'] as $variable => $time) {
                        if (time() > $time) {
                            unset($_SESSION[$namespace][$variable]);
                            unset($_SESSION['__ZF'][$namespace]['ENVT'][$variable]);
                        }
                    }
                    if (empty($_SESSION['__ZF'][$namespace]['ENVT'])) {
                        unset($_SESSION['__ZF'][$namespace]['ENVT']);
                    }
                }

                // Expire Namespace Variables by Global Hop (ENVGH)
                if (isset($namespace_metadata['ENVGH'])) {
                    foreach ($namespace_metadata['ENVGH'] as $variable => $hops) {
                        $_SESSION['__ZF'][$namespace]['ENVGH'][$variable]--;

                        if ($_SESSION['__ZF'][$namespace]['ENVGH'][$variable] === 0) {
                            if (isset($_SESSION[$namespace][$variable])) {
                                parent::$_expiringData[$namespace][$variable] = $_SESSION[$namespace][$variable];
                                unset($_SESSION[$namespace][$variable]);
                            }
                            unset($_SESSION['__ZF'][$namespace]['ENVGH'][$variable]);
                        }
                    }
                    if (empty($_SESSION['__ZF'][$namespace]['ENVGH'])) {
                        unset($_SESSION['__ZF'][$namespace]['ENVGH']);
                    }
                }

                if (isset($namespace) && empty($_SESSION['__ZF'][$namespace])) {
                    unset($_SESSION['__ZF'][$namespace]);
                }
            }
        }

        if (isset($_SESSION['__ZF']) && empty($_SESSION['__ZF'])) {
            unset($_SESSION['__ZF']);
        }
    }


    /**
     * isStarted() - convenience method to determine if the session is already started.
     *
     * @return bool
     */
    public static function isStarted()
    {
        return self::$_sessionStarted;
    }


    /**
     * isRegenerated() - convenience method to determine if session_regenerate_id()
     * has been called during this request by Zend_Session.
     *
     * @return bool
     */
    public static function isRegenerated()
    {
        return ( (self::$_regenerateIdState > 0) ? true : false );
    }


    /**
     * getId() - get the current session id
     *
     * @return string
     */
    public static function getId()
    {
        return session_id();
    }


    /**
     * setId() - set an id to a user specified id
     *
     * @throws Zend_Session_Exception
     * @param string $id
     * @return void
     */
    public static function setId($id)
    {
        if (!self::$_unitTestEnabled && defined('SID')) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception('The session has already been started.  The session id must be set first.');
        }

        if (!self::$_unitTestEnabled && headers_sent($filename, $linenum)) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception("You must call ".__CLASS__.'::'.__FUNCTION__.
                "() before any output has been sent to the browser; output started in {$filename}/{$linenum}");
        }

        if (!is_string($id) || $id === '') {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception('You must provide a non-empty string as a session identifier.');
        }

        session_id($id);
    }


    /**
     * registerValidator() - register a validator that will attempt to validate this session for
     * every future request
     *
     * @param Zend_Session_Validator_Interface $validator
     * @return void
     */
    public static function registerValidator(Zend_Session_Validator_Interface $validator)
    {
        $validator->setup();
    }


    /**
     * stop() - Disable write access.  Optionally disable read (not implemented).
     *
     * @return void
     */
    public static function stop()
    {
        parent::$_writable = false;
    }


    /**
     * writeClose() - Shutdown the sesssion, close writing and detach $_SESSION from the back-end storage mechanism.
     * This will complete the internal data transformation on this request.
     *
     * @param bool $readonly - OPTIONAL remove write access (i.e. throw error if Zend_Session's attempt writes)
     * @return void
     */
    public static function writeClose($readonly = true)
    {
        if (self::$_unitTestEnabled) {
            return;
        }

        if (self::$_writeClosed) {
            return;
        }

        if ($readonly) {
            parent::$_writable = false;
        }

        session_write_close();
        self::$_writeClosed = true;
    }


    /**
     * destroy() - This is used to destroy session data, and optionally, the session cookie itself
     *
     * @param bool $remove_cookie - OPTIONAL remove session id cookie, defaults to true (remove cookie)
     * @param bool $readonly - OPTIONAL remove write access (i.e. throw error if Zend_Session's attempt writes)
     * @return void
     */
    public static function destroy($remove_cookie = true, $readonly = true)
    {
        if (self::$_unitTestEnabled) {
            return;
        }

        if (self::$_destroyed) {
            return;
        }

        if ($readonly) {
            parent::$_writable = false;
        }

        session_destroy();
        self::$_destroyed = true;

        if ($remove_cookie) {
            self::expireSessionCookie();
        }
    }


    /**
     * expireSessionCookie() - Sends an expired session id cookie, causing the client to delete the session cookie
     *
     * @return void
     */
    public static function expireSessionCookie()
    {
        if (self::$_unitTestEnabled) {
            return;
        }

        if (self::$_sessionCookieDeleted) {
            return;
        }

        self::$_sessionCookieDeleted = true;

        if (isset($_COOKIE[session_name()])) {
            $cookie_params = session_get_cookie_params();

            setcookie(
                session_name(),
                false,
                315554400, // strtotime('1980-01-01'),
                $cookie_params['path'],
                $cookie_params['domain'],
                $cookie_params['secure']
                );
        }
    }


    /**
     * _processValidator() - internal function that is called in the existence of VALID metadata
     *
     * @throws Zend_Session_Exception
     * @return void
     */
    private static function _processValidators()
    {
        foreach ($_SESSION['__ZF']['VALID'] as $validator_name => $valid_data) {
            if (!class_exists($validator_name)) {
                // require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($validator_name);
            }
            $validator = new $validator_name;
            if ($validator->validate() === false) {
                /** @see Zend_Session_Exception */
                // require_once 'Zend/Session/Exception.php';
                throw new Zend_Session_Exception("This session is not valid according to {$validator_name}.");
            }
        }
    }


    /**
     * namespaceIsset() - check to see if a namespace is set
     *
     * @param string $namespace
     * @return bool
     */
    public static function namespaceIsset($namespace)
    {
        return parent::_namespaceIsset($namespace);
    }


    /**
     * namespaceUnset() - unset a namespace or a variable within a namespace
     *
     * @param string $namespace
     * @throws Zend_Session_Exception
     * @return void
     */
    public static function namespaceUnset($namespace)
    {
        parent::_namespaceUnset($namespace);
        Zend_Session_Namespace::resetSingleInstance($namespace);
    }


    /**
     * namespaceGet() - get all variables in a namespace
     * Deprecated: Use getIterator() in Zend_Session_Namespace.
     *
     * @param string $namespace
     * @return array
     */
    public static function namespaceGet($namespace)
    {
        return parent::_namespaceGetAll($namespace);
    }


    /**
     * getIterator() - return an iteratable object for use in foreach and the like,
     * this completes the IteratorAggregate interface
     *
     * @throws Zend_Session_Exception
     * @return ArrayObject
     */
    public static function getIterator()
    {
        if (parent::$_readable === false) {
            /** @see Zend_Session_Exception */
            // require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception(parent::_THROW_NOT_READABLE_MSG);
        }

        $spaces  = array();
        if (isset($_SESSION)) {
            $spaces = array_keys($_SESSION);
            foreach($spaces as $key => $space) {
                if (!strncmp($space, '__', 2) || !is_array($_SESSION[$space])) {
                    unset($spaces[$key]);
                }
            }
        }

        return new ArrayObject(array_merge($spaces, array_keys(parent::$_expiringData)));
    }


    /**
     * isWritable() - returns a boolean indicating if namespaces can write (use setters)
     *
     * @return bool
     */
    public static function isWritable()
    {
        return parent::$_writable;
    }


    /**
     * isReadable() - returns a boolean indicating if namespaces can write (use setters)
     *
     * @return bool
     */
    public static function isReadable()
    {
        return parent::$_readable;
    }

}
