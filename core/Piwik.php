<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Exception;
use Piwik\Db\Adapter;
use Piwik\Db\Schema;
use Piwik\Db;

use Piwik\Plugin;
use Piwik\Plugins\UsersManager\API;
use Piwik\Session;
use Piwik\Tracker;
use Piwik\View;

/**
 * @see core/Translate.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';

/**
 * Main piwik helper class.
 * Contains static functions you can call from the plugins.
 *
 * @package Piwik
 */
class Piwik
{
    /**
     * Piwik periods
     * @var array
     */
    public static $idPeriods = array(
        'day'   => 1,
        'week'  => 2,
        'month' => 3,
        'year'  => 4,
        'range' => 5,
    );

    const LABEL_ID_GOAL_IS_ECOMMERCE_CART = 'ecommerceAbandonedCart';
    const LABEL_ID_GOAL_IS_ECOMMERCE_ORDER = 'ecommerceOrder';

    /**
     * Trigger E_USER_ERROR with optional message
     *
     * @param string $message
     */
    static public function error($message = '')
    {
        trigger_error($message, E_USER_ERROR);
    }

    /**
     * Display the message in a nice red font with a nice icon
     * ... and dies
     *
     * @param string $message
     * @api
     */
    static public function exitWithErrorMessage($message)
    {
        if (!Common::isPhpCliMode()) {
            @header('Content-Type: text/html; charset=utf-8');
        }

        $output = "<style>a{color:red;}</style>\n" .
            "<div style='color:red;font-family:Georgia;font-size:120%'>" .
            "<p><img src='plugins/Zeitgeist/images/error_medium.png' style='vertical-align:middle; float:left;padding:20 20 20 20' />" .
            $message .
            "</p></div>";
        print($output);
        exit;
    }

    /**
     * Computes the division of i1 by i2. If either i1 or i2 are not number, or if i2 has a value of zero
     * we return 0 to avoid the division by zero.
     *
     * @param number $i1
     * @param number $i2
     * @return number The result of the division or zero
     */
    static public function secureDiv($i1, $i2)
    {
        if (is_numeric($i1) && is_numeric($i2) && floatval($i2) != 0) {
            return $i1 / $i2;
        }
        return 0;
    }

    /**
     * Safely compute a percentage.  Return 0 to avoid division by zero.
     *
     * @param number $dividend
     * @param number $divisor
     * @param int $precision
     * @return number
     */
    static public function getPercentageSafe($dividend, $divisor, $precision = 0)
    {
        if ($divisor == 0) {
            return 0;
        }
        return round(100 * $dividend / $divisor, $precision);
    }

    /**
     * Returns the Javascript code to be inserted on every page to track
     *
     * @param int $idSite
     * @param string $piwikUrl http://path/to/piwik/directory/
     * @return string
     */
    static public function getJavascriptCode($idSite, $piwikUrl)
    {
        $jsCode = file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Zeitgeist/templates/javascriptCode.tpl");
        $jsCode = htmlentities($jsCode);
        preg_match('~^(http|https)://(.*)$~D', $piwikUrl, $matches);
        $piwikUrl = @$matches[2];
        $jsCode = str_replace('{$idSite}', $idSite, $jsCode);
        $jsCode = str_replace('{$piwikUrl}', Common::sanitizeInputValue($piwikUrl), $jsCode);
        return $jsCode;
    }

    /**
     * Generate a title for image tags
     *
     * @return string
     */
    static public function getRandomTitle()
    {
        static $titles = array(
            'Web analytics',
            'Real Time Web Analytics',
            'Analytics',
            'Real Time Analytics',
            'Analytics in Real time',
            'Open Source Analytics',
            'Open Source Web Analytics',
            'Free Website Analytics',
            'Free Web Analytics',
            'Analytics Platform',
        );
        $id = abs(intval(md5(Url::getCurrentHost())));
        $title = $titles[$id % count($titles)];
        return $title;
    }

    /*
     * Access
     */

    /**
     * Get current user email address
     *
     * @return string
     * @api
     */
    static public function getCurrentUserEmail()
    {
        if (!Piwik::isUserIsSuperUser()) {
            $user = API::getInstance()->getUser(Piwik::getCurrentUserLogin());
            return $user['email'];
        }
        return self::getSuperUserEmail();
    }

    /**
     * Returns Super User login
     *
     * @return string
     * @api
     */
    static public function getSuperUserLogin()
    {
        return Access::getInstance()->getSuperUserLogin();
    }

    /**
     * Returns Super User email
     *
     * @return string
     * @api
     */
    static public function getSuperUserEmail()
    {
        $superuser = Config::getInstance()->superuser;
        return $superuser['email'];
    }

    /**
     * Get current user login
     *
     * @return string  login ID
     * @api
     */
    static public function getCurrentUserLogin()
    {
        return Access::getInstance()->getLogin();
    }

    /**
     * Get current user's token auth
     *
     * @return string  Token auth
     * @api
     */
    static public function getCurrentUserTokenAuth()
    {
        return Access::getInstance()->getTokenAuth();
    }

    /**
     * Returns true if the current user is either the super user, or the user $theUser
     * Used when modifying user preference: this usually requires super user or being the user itself.
     *
     * @param string $theUser
     * @return bool
     *
     * @api
     */
    static public function isUserIsSuperUserOrTheUser($theUser)
    {
        try {
            self::checkUserIsSuperUserOrTheUser($theUser);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check that current user is either the specified user or the superuser
     *
     * @param string $theUser
     * @throws NoAccessException  if the user is neither the super user nor the user $theUser
     * @api
     */
    static public function checkUserIsSuperUserOrTheUser($theUser)
    {
        try {
            if (Piwik::getCurrentUserLogin() !== $theUser) {
                // or to the super user
                Piwik::checkUserIsSuperUser();
            }
        } catch (NoAccessException $e) {
            throw new NoAccessException(Piwik::translate('General_ExceptionCheckUserIsSuperUserOrTheUser', array($theUser)));
        }
    }

    /**
     * Returns true if the current user is the Super User
     *
     * @return bool
     * @api
     */
    static public function isUserIsSuperUser()
    {
        try {
            self::checkUserIsSuperUser();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Is user the anonymous user?
     *
     * @return bool  True if anonymouse; false otherwise
     * @api
     */
    static public function isUserIsAnonymous()
    {
        return Piwik::getCurrentUserLogin() == 'anonymous';
    }

    /**
     * Checks if user is not the anonymous user.
     *
     * @throws NoAccessException  if user is anonymous.
     * @api
     */
    static public function checkUserIsNotAnonymous()
    {
        if (self::isUserIsAnonymous()) {
            throw new NoAccessException(Piwik::translate('General_YouMustBeLoggedIn'));
        }
    }

    /**
     * Helper method user to set the current as Super User.
     * This should be used with great care as this gives the user all permissions.
     *
     * @param bool $bool true to set current user as super user
     * @api
     */
    static public function setUserIsSuperUser($bool = true)
    {
        Access::getInstance()->setSuperUser($bool);
    }

    /**
     * Check that user is the superuser
     *
     * @throws Exception if not the superuser
     * @api
     */
    static public function checkUserIsSuperUser()
    {
        Access::getInstance()->checkUserIsSuperUser();
    }

    /**
     * Returns true if the user has admin access to the sites
     *
     * @param mixed $idSites
     *
     * @return bool
     *
     * @api
     */
    static public function isUserHasAdminAccess($idSites)
    {
        try {
            self::checkUserHasAdminAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has admin access to the sites
     *
     * @param mixed $idSites
     * @throws Exception if user doesn't have admin access to the sites
     * @api
     */
    static public function checkUserHasAdminAccess($idSites)
    {
        Access::getInstance()->checkUserHasAdminAccess($idSites);
    }

    /**
     * Returns true if the user has admin access to any sites
     *
     * @return bool
     * @api
     */
    static public function isUserHasSomeAdminAccess()
    {
        try {
            self::checkUserHasSomeAdminAccess();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has admin access to any sites
     *
     * @throws Exception if user doesn't have admin access to any sites
     * @api
     */
    static public function checkUserHasSomeAdminAccess()
    {
        Access::getInstance()->checkUserHasSomeAdminAccess();
    }

    /**
     * Returns true if the user has view access to the sites
     *
     * @param mixed $idSites
     * @return bool
     *
     * @api
     */
    static public function isUserHasViewAccess($idSites)
    {
        try {
            self::checkUserHasViewAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has view access to the sites
     *
     * @param mixed $idSites
     * @throws Exception if user doesn't have view access to sites
     *
     * @api
     */
    static public function checkUserHasViewAccess($idSites)
    {
        Access::getInstance()->checkUserHasViewAccess($idSites);
    }

    /**
     * Returns true if the user has view access to any sites
     *
     * @return bool
     *
     * @api
     */
    static public function isUserHasSomeViewAccess()
    {
        try {
            self::checkUserHasSomeViewAccess();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has view access to any sites
     *
     * @throws Exception if user doesn't have view access to any sites
     *
     * @api
     */
    static public function checkUserHasSomeViewAccess()
    {
        Access::getInstance()->checkUserHasSomeViewAccess();
    }

    /*
     * Current module, action, plugin
     */

    /**
     * Returns the name of the Login plugin currently being used.
     * Must be used since it is not allowed to hardcode 'Login' in URLs
     * in case another Login plugin is being used.
     *
     * @return string
     */
    static public function getLoginPluginName()
    {
        return Registry::get('auth')->getName();
    }

    /**
     * Returns the plugin currently being used to display the page
     *
     * @return Plugin
     */
    static public function getCurrentPlugin()
    {
        return \Piwik\Plugin\Manager::getInstance()->getLoadedPlugin(Piwik::getModule());
    }

    /**
     * Returns the current module read from the URL (eg. 'API', 'UserSettings', etc.)
     *
     * @return string
     *
     * @api
     */
    static public function getModule()
    {
        return Common::getRequestVar('module', '', 'string');
    }

    /**
     * Returns the current action read from the URL
     *
     * @return string
     *
     * @api
     */
    static public function getAction()
    {
        return Common::getRequestVar('action', '', 'string');
    }

    /**
     * Helper method used in API function to introduce array elements in API parameters.
     * Array elements can be passed by comma separated values, or using the notation
     * array[]=value1&array[]=value2 in the URL.
     * This function will handle both cases and return the array.
     *
     * @param array|string $columns
     * @return array
     */
    static public function getArrayFromApiParameter($columns)
    {
        if (empty($columns)) {
            return array();
        }
        if (is_array($columns)) {
            return $columns;
        }
        $array = explode(',', $columns);
        $array = array_unique($array);
        return $array;
    }

    /**
     * Redirect to module (and action)
     *
     * @param string $newModule Target module
     * @param string $newAction Target action
     * @param array $parameters Parameters to modify in the URL
     * @return bool  false if the URL to redirect to is already this URL
     *
     * @api
     */
    static public function redirectToModule($newModule, $newAction = '', $parameters = array())
    {
        $newUrl = 'index.php' . Url::getCurrentQueryStringWithParametersModified(
                array('module' => $newModule, 'action' => $newAction)
                + $parameters
            );
        Url::redirectToUrl($newUrl);
    }

    /*
     * User input validation
     */

    /**
     * Returns true if the email is a valid email
     *
     * @param string $email
     * @return bool
     *
     * @api
     */
    static public function isValidEmailString($email)
    {
        return (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,7}$/D', $email) > 0);
    }

    /**
     * Returns true if the login is valid.
     * Warning: does not check if the login already exists! You must use UsersManager_API->userExists as well.
     *
     * @param string $userLogin
     * @throws Exception
     * @return bool
     */
    static public function checkValidLoginString($userLogin)
    {
        if (!SettingsPiwik::isUserCredentialsSanityCheckEnabled()
            && !empty($userLogin)
        ) {
            return;
        }
        $loginMinimumLength = 3;
        $loginMaximumLength = 100;
        $l = strlen($userLogin);
        if (!($l >= $loginMinimumLength
            && $l <= $loginMaximumLength
            && (preg_match('/^[A-Za-z0-9_.@+-]*$/D', $userLogin) > 0))
        ) {
            throw new Exception(Piwik::translateException('UsersManager_ExceptionInvalidLoginFormat', array($loginMinimumLength, $loginMaximumLength)));
        }
    }

    /**
     * Utility function that checks if an object type is in a set of types.
     *
     * @param mixed $o
     * @param array $types List of class names that $o is expected to be one of.
     * @throws Exception if $o is not an instance of the types contained in $types.
     */
    static public function checkObjectTypeIs($o, $types)
    {
        foreach ($types as $type) {
            if ($o instanceof $type) {
                return;
            }
        }

        $oType = is_object($o) ? get_class($o) : gettype($o);
        throw new Exception("Invalid variable type '$oType', expected one of following: " . implode(', ', $types));
    }

    /**
     * Returns true if an array is an associative array, false if otherwise.
     *
     * This method determines if an array is associative by checking that the
     * first element's key is 0, and that each successive element's key is
     * one greater than the last.
     *
     * @param array $array
     * @return bool
     */
    static public function isAssociativeArray($array)
    {
        reset($array);
        if (!is_numeric(key($array))
            || key($array) != 0
        ) // first key must be 0
        {
            return true;
        }

        // check that each key is == next key - 1 w/o actually indexing the array
        while (true) {
            $current = key($array);

            next($array);
            $next = key($array);

            if ($next === null) {
                break;
            } else if ($current + 1 != $next) {
                return true;
            }
        }

        return false;
    }


    /**
     * Returns the class name of an object without its namespace.
     *
     * @param mixed|string $object
     * @return string
     */
    public static function getUnnamespacedClassName($object)
    {
        $className = is_string($object) ? $object : get_class($object);
        $parts = explode('\\', $className);
        return end($parts);
    }


    /**
     * Post an event to the dispatcher which will notice the observers.
     *
     * @param string $eventName The event name.
     * @param array $params The parameter array to forward to observer callbacks.
     * @param bool $pending
     * @param null $plugins
     * @return void
     * @api
     */
    public static function postEvent($eventName, $params = array(), $pending = false, $plugins = null)
    {
        EventDispatcher::getInstance()->postEvent($eventName, $params, $pending, $plugins);
    }

    /**
     * Register an action to execute for a given event
     *
     * @param string $eventName Name of event
     * @param callable $function Callback hook
     * @api
     */
    public static function addAction($eventName, $function)
    {
        EventDispatcher::getInstance()->addObserver($eventName, $function);
    }

    /**
     * Posts an event if we are currently running tests. Whether we are running tests is
     * determined by looking for the PIWIK_TEST_MODE constant.
     */
    public static function postTestEvent($eventName, $params = array(), $pending = false, $plugins = null)
    {
        if (defined('PIWIK_TEST_MODE')) {
            Piwik::postEvent($eventName, $params, $pending, $plugins);
        }
    }

    /**
     * Returns translated string or given message if translation is not found.
     *
     * @param string $string Translation string index
     * @param array|string|int $args sprintf arguments
     * @return string
     * @api
     */
    public static function translate($string, $args = array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }

        if (strpos($string, "_") !== false) {
            list($plugin, $key) = explode("_", $string, 2);
            if (isset($GLOBALS['Piwik_translations'][$plugin]) && isset($GLOBALS['Piwik_translations'][$plugin][$key])) {
                $string = $GLOBALS['Piwik_translations'][$plugin][$key];
            }
        }
        if (count($args) == 0) {
            return $string;
        }
        return vsprintf($string, $args);
    }


    /**
     * Returns translated string or given message if translation is not found.
     * This function does not throw any exception. Use it to translate exceptions.
     *
     * @param string $message Translation string index
     * @param array $args sprintf arguments
     * @return string
     * @api
     */
    public static function translateException($message, $args = array())
    {
        try {
            return Piwik::translate($message, $args);
        } catch (Exception $e) {
            return $message;
        }
    }

}
