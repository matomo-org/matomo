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
use Piwik\Log\ScreenFormatter;
use Piwik\Plugin;
use Piwik\Plugins\UsersManager\API;
use Piwik\Session;
use Piwik\Tracker;
use Piwik\View;
use Zend_Registry;

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
    const COMPRESSED_FILE_LOCATION = '/tmp/assets/';

    /**
     * Piwik periods
     * @var array
     */
    public static $idPeriods = array(
        'day' => 1,
        'week' => 2,
        'month' => 3,
        'year' => 4,
        'range' => 5,
    );


    const LABEL_ID_GOAL_IS_ECOMMERCE_CART = 'ecommerceAbandonedCart';
    const LABEL_ID_GOAL_IS_ECOMMERCE_ORDER = 'ecommerceOrder';

    /**
     * Logging and error handling
     *
     * @var bool|null
     */
    public static $shouldLog = null;

    /**
     * Log a message TODO: remove
     *
     * @param string $message
     */
    static public function log($message = '')
    {
        Log::i("none", $message);
    }

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
     */
    static public function exitWithErrorMessage($message)
    {
        $output = "<style>a{color:red;}</style>\n" .
            "<div style='color:red;font-family:Georgia;font-size:120%'>" .
            "<p><img src='plugins/Zeitgeist/images/error_medium.png' style='vertical-align:middle; float:left;padding:20 20 20 20' />" .
            $message .
            "</p></div>";
        print(ScreenFormatter::getFormattedString($output));
        exit;
    }

    /*
     * Amounts, Percentages, Currency, Time, Math Operations, and Pretty Printing
     */

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
     * @param string $piwikUrl  http://path/to/piwik/directory/
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
     */
    static public function getSuperUserLogin()
    {
        return Access::getInstance()->getSuperUserLogin();
    }

    /**
     * Returns Super User email
     *
     * @return string
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
     */
    static public function getCurrentUserLogin()
    {
        return Access::getInstance()->getLogin();
    }

    /**
     * Get current user's token auth
     *
     * @return string  Token auth
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
     */
    static public function checkUserIsSuperUserOrTheUser($theUser)
    {
        try {
            if (Piwik::getCurrentUserLogin() !== $theUser) {
                // or to the super user
                Piwik::checkUserIsSuperUser();
            }
        } catch (NoAccessException $e) {
            throw new NoAccessException(Piwik_Translate('General_ExceptionCheckUserIsSuperUserOrTheUser', array($theUser)));
        }
    }

    /**
     * Returns true if the current user is the Super User
     *
     * @return bool
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
     */
    static public function isUserIsAnonymous()
    {
        return Piwik::getCurrentUserLogin() == 'anonymous';
    }

    /**
     * Checks if user is not the anonymous user.
     *
     * @throws NoAccessException  if user is anonymous.
     */
    static public function checkUserIsNotAnonymous()
    {
        if (self::isUserIsAnonymous()) {
            throw new NoAccessException(Piwik_Translate('General_YouMustBeLoggedIn'));
        }
    }

    /**
     * Helper method user to set the current as Super User.
     * This should be used with great care as this gives the user all permissions.
     *
     * @param bool $bool  true to set current user as super user
     */
    static public function setUserIsSuperUser($bool = true)
    {
        Access::getInstance()->setSuperUser($bool);
    }

    /**
     * Check that user is the superuser
     *
     * @throws Exception if not the superuser
     */
    static public function checkUserIsSuperUser()
    {
        Access::getInstance()->checkUserIsSuperUser();
    }

    /**
     * Returns true if the user has admin access to the sites
     *
     * @param mixed $idSites
     * @return bool
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
     */
    static public function checkUserHasAdminAccess($idSites)
    {
        Access::getInstance()->checkUserHasAdminAccess($idSites);
    }

    /**
     * Returns true if the user has admin access to any sites
     *
     * @return bool
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
     */
    static public function checkUserHasViewAccess($idSites)
    {
        Access::getInstance()->checkUserHasViewAccess($idSites);
    }

    /**
     * Returns true if the user has view access to any sites
     *
     * @return bool
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
        return \Zend_Registry::get('auth')->getName();
    }

    /**
     * Returns the plugin currently being used to display the page
     *
     * @return Plugin
     */
    static public function getCurrentPlugin()
    {
        return \Piwik\PluginsManager::getInstance()->getLoadedPlugin(Piwik::getModule());
    }

    /**
     * Returns the current module read from the URL (eg. 'API', 'UserSettings', etc.)
     *
     * @return string
     */
    static public function getModule()
    {
        return Common::getRequestVar('module', '', 'string');
    }

    /**
     * Returns the current action read from the URL
     *
     * @return string
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
     * @param string $newModule   Target module
     * @param string $newAction   Target action
     * @param array $parameters  Parameters to modify in the URL
     * @return bool  false if the URL to redirect to is already this URL
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
            throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidLoginFormat', array($loginMinimumLength, $loginMaximumLength)));
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
}
