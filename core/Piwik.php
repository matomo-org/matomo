<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Period\Day;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Period\Week;
use Piwik\Period\Year;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Translation\Translator;

/**
 * @see core/Translate.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';

/**
 * Main piwik helper class.
 *
 * Contains helper methods for a variety of common tasks. Plugin developers are
 * encouraged to reuse these methods as much as possible.
 */
class Piwik
{
    /**
     * Piwik periods
     * @var array
     */
    public static $idPeriods = array(
        'day'   => Day::PERIOD_ID,
        'week'  => Week::PERIOD_ID,
        'month' => Month::PERIOD_ID,
        'year'  => Year::PERIOD_ID,
        'range' => Range::PERIOD_ID,
    );

    /**
     * The idGoal query parameter value for the special 'abandoned carts' goal.
     *
     * @api
     */
    const LABEL_ID_GOAL_IS_ECOMMERCE_CART = 'ecommerceAbandonedCart';

    /**
     * The idGoal query parameter value for the special 'ecommerce' goal.
     *
     * @api
     */
    const LABEL_ID_GOAL_IS_ECOMMERCE_ORDER = 'ecommerceOrder';

    /**
     * Trigger E_USER_ERROR with optional message
     *
     * @param string $message
     */
    public static function error($message = '')
    {
        trigger_error($message, E_USER_ERROR);
    }

    /**
     * Display the message in a nice red font with a nice icon
     * ... and dies
     *
     * @param string $message
     */
    public static function exitWithErrorMessage($message)
    {
        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $output = "<style>a{color:red;}</style>\n" .
            "<div style='color:red;font-size:120%'>" .
            "<p><img src='plugins/Morpheus/images/error_medium.png' style='vertical-align:middle; float:left;padding:20px' />" .
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
    public static function secureDiv($i1, $i2)
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
    public static function getPercentageSafe($dividend, $divisor, $precision = 0)
    {
        return self::getQuotientSafe(100 * $dividend, $divisor, $precision);
    }

    /**
     * Safely compute a ratio. Returns 0 if divisor is 0 (to avoid division by 0 error).
     *
     * @param number $dividend
     * @param number $divisor
     * @param int $precision
     * @return number
     */
    public static function getQuotientSafe($dividend, $divisor, $precision = 0)
    {
        if ($divisor == 0) {
            return 0;
        }
        return round($dividend / $divisor, $precision);
    }

    /**
     * Generate a title for image tags
     *
     * @return string
     */
    public static function getRandomTitle()
    {
        static $titles = array(
            'Web analytics',
            'Open analytics platform',
            'Real Time Web Analytics',
            'Analytics',
            'Real Time Analytics',
            'Analytics in Real time',
            'Analytics Platform',
            'Data Platform',
        );
        $id = abs(intval(md5(Url::getCurrentHost())));
        $title = $titles[$id % count($titles)];
        return $title;
    }

    /*
     * Access
     */

    /**
     * Returns the current user's email address.
     *
     * @return string
     * @api
     */
    public static function getCurrentUserEmail()
    {
        $user = APIUsersManager::getInstance()->getUser(Piwik::getCurrentUserLogin());
        return $user['email'];
    }

    /**
     * Get a list of all email addresses having Super User access.
     *
     * @return array
     */
    public static function getAllSuperUserAccessEmailAddresses()
    {
        $emails = array();

        try {
            $superUsers = APIUsersManager::getInstance()->getUsersHavingSuperUserAccess();
        } catch (\Exception $e) {
            return $emails;
        }

        foreach ($superUsers as $superUser) {
            $emails[$superUser['login']] = $superUser['email'];
        }

        return $emails;
    }

    /**
     * Returns the current user's username.
     *
     * @return string
     * @api
     */
    public static function getCurrentUserLogin()
    {
        $login = Access::getInstance()->getLogin();

        if (empty($login)) {
            return 'anonymous';
        }
        return $login;
    }

    /**
     * Returns the current user's token auth.
     *
     * @return string
     * @api
     */
    public static function getCurrentUserTokenAuth()
    {
        return Access::getInstance()->getTokenAuth();
    }

    /**
     * Returns `true` if the current user is either the Super User or the user specified by
     * `$theUser`.
     *
     * @param string $theUser A username.
     * @return bool
     * @api
     */
    public static function hasUserSuperUserAccessOrIsTheUser($theUser)
    {
        try {
            self::checkUserHasSuperUserAccessOrIsTheUser($theUser);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check that the current user is either the specified user or the superuser.
     *
     * @param string $theUser A username.
     * @throws NoAccessException If the user is neither the Super User nor the user `$theUser`.
     * @api
     */
    public static function checkUserHasSuperUserAccessOrIsTheUser($theUser)
    {
        try {
            if (Piwik::getCurrentUserLogin() !== $theUser) {
                // or to the Super User
                Piwik::checkUserHasSuperUserAccess();
            }
        } catch (NoAccessException $e) {
            throw new NoAccessException(Piwik::translate('General_ExceptionCheckUserHasSuperUserAccessOrIsTheUser', array($theUser)));
        }
    }

    /**
     * Check whether the given user has superuser access.
     *
     * @param string $theUser A username.
     * @return bool
     * @api
     */
    public static function hasTheUserSuperUserAccess($theUser)
    {
        if (empty($theUser)) {
            return false;
        }

        if (Piwik::getCurrentUserLogin() === $theUser && Piwik::hasUserSuperUserAccess()) {
            return true;
        }

        try {
            $superUsers = APIUsersManager::getInstance()->getUsersHavingSuperUserAccess();
        } catch (\Exception $e) {
            return false;
        }

        foreach ($superUsers as $superUser) {
            if ($theUser === $superUser['login']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the current user has Super User access.
     *
     * @return bool
     * @api
     */
    public static function hasUserSuperUserAccess()
    {
        try {
            $hasAccess = Access::getInstance()->hasSuperUserAccess();

            return $hasAccess;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns true if the current user is the special **anonymous** user or not.
     *
     * @return bool
     * @api
     */
    public static function isUserIsAnonymous()
    {
        $currentUserLogin = Piwik::getCurrentUserLogin();
        return $currentUserLogin == 'anonymous';
    }

    /**
     * Checks that the user is not the anonymous user.
     *
     * @throws NoAccessException if the current user is the anonymous user.
     * @api
     */
    public static function checkUserIsNotAnonymous()
    {
        if (Access::getInstance()->hasSuperUserAccess()) {
            return;
        }
        if (self::isUserIsAnonymous()) {
            throw new NoAccessException(Piwik::translate('General_YouMustBeLoggedIn'));
        }
    }

    /**
     * Helper method user to set the current as superuser.
     * This should be used with great care as this gives the user all permissions.
     *
     * This method is deprecated, use {@link Access::doAsSuperUser()} instead.
     *
     * @param bool $bool true to set current user as Super User
     * @deprecated
     */
    public static function setUserHasSuperUserAccess($bool = true)
    {
        Access::getInstance()->setSuperUserAccess($bool);
    }

    /**
     * Check that the current user has superuser access.
     *
     * @throws Exception if the current user is not the superuser.
     * @api
     */
    public static function checkUserHasSuperUserAccess()
    {
        Access::getInstance()->checkUserHasSuperUserAccess();
    }

    /**
     * Returns `true` if the user has admin access to the requested sites, `false` if otherwise.
     *
     * @param int|array $idSites The list of site IDs to check access for.
     * @return bool
     * @api
     */
    public static function isUserHasAdminAccess($idSites)
    {
        try {
            self::checkUserHasAdminAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks that the current user has admin access to the requested list of sites.
     *
     * @param int|array $idSites One or more site IDs to check access for.
     * @throws Exception If user doesn't have admin access.
     * @api
     */
    public static function checkUserHasAdminAccess($idSites)
    {
        Access::getInstance()->checkUserHasAdminAccess($idSites);
    }

    /**
     * Returns `true` if the current user has admin access to at least one site.
     *
     * @return bool
     * @api
     */
    public static function isUserHasSomeAdminAccess()
    {
        return Access::getInstance()->isUserHasSomeAdminAccess();
    }

    /**
     * Checks that the current user has admin access to at least one site.
     *
     * @throws Exception if user doesn't have admin access to any site.
     * @api
     */
    public static function checkUserHasSomeAdminAccess()
    {
        Access::getInstance()->checkUserHasSomeAdminAccess();
    }

    /**
     * Returns `true` if the user has view access to the requested list of sites.
     *
     * @param int|array $idSites One or more site IDs to check access for.
     * @return bool
     * @api
     */
    public static function isUserHasViewAccess($idSites)
    {
        try {
            self::checkUserHasViewAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks that the current user has view access to the requested list of sites
     *
     * @param int|array $idSites The list of site IDs to check access for.
     * @throws Exception if the current user does not have view access to every site in the list.
     * @api
     */
    public static function checkUserHasViewAccess($idSites)
    {
        Access::getInstance()->checkUserHasViewAccess($idSites);
    }

    /**
     * Returns `true` if the current user has view access to at least one site.
     *
     * @return bool
     * @api
     */
    public static function isUserHasSomeViewAccess()
    {
        try {
            self::checkUserHasSomeViewAccess();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks that the current user has view access to at least one site.
     *
     * @throws Exception if user doesn't have view access to any site.
     * @api
     */
    public static function checkUserHasSomeViewAccess()
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
     * @api
     */
    public static function getLoginPluginName()
    {
        return StaticContainer::get('Piwik\Auth')->getName();
    }

    /**
     * Returns the plugin currently being used to display the page
     *
     * @return Plugin
     */
    public static function getCurrentPlugin()
    {
        return \Piwik\Plugin\Manager::getInstance()->getLoadedPlugin(Piwik::getModule());
    }

    /**
     * Returns the current module read from the URL (eg. 'API', 'DevicesDetection', etc.)
     *
     * @return string
     */
    public static function getModule()
    {
        return Common::getRequestVar('module', '', 'string');
    }

    /**
     * Returns the current action read from the URL
     *
     * @return string
     */
    public static function getAction()
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
    public static function getArrayFromApiParameter($columns)
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
     * Redirects the current request to a new module and action.
     *
     * @param string $newModule The target module, eg, `'UserCountry'`.
     * @param string $newAction The target controller action, eg, `'index'`.
     * @param array $parameters The query parameter values to modify before redirecting.
     * @api
     */
    public static function redirectToModule($newModule, $newAction = '', $parameters = array())
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
     * Returns `true` if supplied the email address is a valid.
     *
     * @param string $emailAddress
     * @return bool
     * @api
     */
    public static function isValidEmailString($emailAddress)
    {
        /** @var \Zend_Validate_EmailAddress $zendEmailValidator */
        $zendEmailValidator = StaticContainer::get('Zend_Validate_EmailAddress');
        return $zendEmailValidator->isValid($emailAddress);
    }

    /**
     * Returns `true` if the login is valid.
     *
     * _Warning: does not check if the login already exists! You must use UsersManager_API->userExists as well._
     *
     * @param string $userLogin
     * @throws Exception
     * @return bool
     */
    public static function checkValidLoginString($userLogin)
    {
        if (!SettingsPiwik::isUserCredentialsSanityCheckEnabled()
            && !empty($userLogin)
        ) {
            return;
        }
        $loginMinimumLength = 2;
        $loginMaximumLength = 100;
        $l = strlen($userLogin);
        if (!($l >= $loginMinimumLength
            && $l <= $loginMaximumLength
            && (preg_match('/^[A-Za-zÄäÖöÜüß0-9_.@+-]*$/D', $userLogin) > 0))
        ) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidLoginFormat', array($loginMinimumLength, $loginMaximumLength)));
        }
    }

    /**
     * Utility function that checks if an object type is in a set of types.
     *
     * @param mixed $o
     * @param array $types List of class names that $o is expected to be one of.
     * @throws Exception if $o is not an instance of the types contained in $types.
     */
    public static function checkObjectTypeIs($o, $types)
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
    public static function isAssociativeArray($array)
    {
        reset($array);
        if (!is_numeric(key($array))
            || key($array) != 0
        ) {
            // first key must be 0

            return true;
        }

        // check that each key is == next key - 1 w/o actually indexing the array
        while (true) {
            $current = key($array);

            next($array);
            $next = key($array);

            if ($next === null) {
                break;
            } elseif ($current + 1 != $next) {
                return true;
            }
        }

        return false;
    }

    public static function isMultiDimensionalArray($array)
    {
        $first = reset($array);
        foreach ($array as $first) {
            if (is_array($first)) {
                // Yes, this is a multi dim array
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
     * Post an event to Piwik's event dispatcher which will execute the event's observers.
     *
     * @param string $eventName The event name.
     * @param array $params The parameter array to forward to observer callbacks.
     * @param bool $pending If true, plugins that are loaded after this event is fired will
     *                      have their observers for this event executed.
     * @param array|null $plugins The list of plugins to execute observers for. If null, all
     *                            plugin observers will be executed.
     * @api
     */
    public static function postEvent($eventName, $params = array(), $pending = false, $plugins = null)
    {
        EventDispatcher::getInstance()->postEvent($eventName, $params, $pending, $plugins);
    }

    /**
     * Register an observer to an event.
     *
     * **_Note: Observers should normally be defined in plugin objects. It is unlikely that you will
     * need to use this function._**
     *
     * @param string $eventName The event name.
     * @param callable|array $function The observer.
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
     * Returns an internationalized string using a translation token. If a translation
     * cannot be found for the toke, the token is returned.
     *
     * @param string $translationId Translation ID, eg, `'General_Date'`.
     * @param array|string|int $args `sprintf` arguments to be applied to the internationalized
     *                               string.
     * @param string|null $language Optionally force the language.
     * @return string The translated string or `$translationId`.
     * @api
     */
    public static function translate($translationId, $args = array(), $language = null)
    {
        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        return $translator->translate($translationId, $args, $language);
    }

    /**
     * Executes a callback with superuser privileges, making sure those privileges are rescinded
     * before this method exits. Privileges will be rescinded even if an exception is thrown.
     *
     * @param callback $function The callback to execute. Should accept no arguments.
     * @return mixed The result of `$function`.
     * @throws Exception rethrows any exceptions thrown by `$function`.
     * @api
     */
    public static function doAsSuperUser($function)
    {
        $isSuperUser = self::hasUserSuperUserAccess();

        self::setUserHasSuperUserAccess();

        try {
            $result = $function();
        } catch (Exception $ex) {
            self::setUserHasSuperUserAccess($isSuperUser);

            throw $ex;
        }

        self::setUserHasSuperUserAccess($isSuperUser);

        return $result;
    }
}
