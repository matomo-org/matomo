<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\Mock;

use Piwik\NoAccessException;
use Piwik\Plugins\SitesManager\API;
use Piwik\Site as PiwikSite;
use Exception;

/**
 * FakeAccess for UnitTests
 * @since 2.8.0
 */
class FakeAccess
{
    public static $superUser = false;
    public static $idSitesAdmin = array();
    public static $idSitesView = array();
    public static $identity = 'superUserLogin';
    public static $superUserLogin = 'superUserLogin';

    public static function clearAccess($superUser = false, $idSitesAdmin = array(), $idSitesView = array(), $identity = 'superUserLogin')
    {
        self::$superUser    = $superUser;
        self::$idSitesAdmin = $idSitesAdmin;
        self::$idSitesView  = $idSitesView;
        self::$identity     = $identity;
    }

    public function getTokenAuth()
    {
        return false;
    }

    public function __construct($superUser = false, $idSitesAdmin = array(), $idSitesView = array(), $identity = 'superUserLogin')
    {
        self::clearAccess($superUser, $idSitesAdmin, $idSitesView, $identity);
    }

    public static function setIdSitesAdmin($ids)
    {
        self::$superUser    = false;
        self::$idSitesAdmin = $ids;
    }

    public static function setIdSitesView($ids)
    {
        self::$superUser   = false;
        self::$idSitesView = $ids;
    }

    public static function hasSuperUserAccess()
    {
        return self::$superUser;
    }

    public static function checkUserHasSuperUserAccess()
    {
        if (!self::$superUser) {
            throw new NoAccessException("checkUserHasSuperUserAccess Fake exception // string not to be tested");
        }
    }

    public static function setSuperUserAccess($bool = true)
    {
        self::$superUser = $bool;
    }

    public static function reloadAccess()
    {
        return true;
    }

    public static function checkUserHasAdminAccess($idSites)
    {
        if (!self::$superUser) {
            $websitesAccess = self::$idSitesAdmin;
        } else {
            $websitesAccess = API::getInstance()->getAllSitesId();
        }

        $idSites = PiwikSite::getIdSitesFromIdSitesString($idSites);

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new NoAccessException("checkUserHasAdminAccess Fake exception // string not to be tested");
            }
        }
    }

    //means at least view access
    public static function checkUserHasViewAccess($idSites)
    {
        if (self::$superUser) {
            return;
        }

        $websitesAccess = array_merge(self::$idSitesView, self::$idSitesAdmin);

        if (!is_array($idSites)) {
            if ($idSites == 'all') {
                $idSites = API::getInstance()->getAllSitesId();
            } else {
                $idSites = explode(',', $idSites);
            }
        }

        if (empty($websitesAccess)) {
            throw new NoAccessException("checkUserHasViewAccess Fake exception // string not to be tested");
        }

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new NoAccessException("checkUserHasViewAccess Fake exception // string not to be tested");
            }
        }
    }

    public static function checkUserHasSomeViewAccess()
    {
        if (!self::$superUser) {
            if (count(self::$idSitesView) == 0) {
                throw new NoAccessException("checkUserHasSomeViewAccess Fake exception // string not to be tested");
            }
        } else {
            return;
        }
    }

    //means at least view access
    public static function checkUserHasSomeAdminAccess()
    {
        if (!self::$superUser) {
            if (count(self::$idSitesAdmin) == 0) {
                throw new NoAccessException("checkUserHasSomeAdminAccess Fake exception // string not to be tested");
            }
        } else {
            return; //Super User has some admin rights
        }
    }

    public static function getLogin()
    {
        return self::$identity;
    }

    public static function getSitesIdWithAdminAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return self::$idSitesAdmin;
    }

    public static function getSitesIdWithViewAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return self::$idSitesView;
    }

    public static function getSitesIdWithAtLeastViewAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return array_merge(self::$idSitesView, self::$idSitesAdmin);
    }

    public function getRawSitesWithSomeViewAccess($login)
    {
        $result = array();

        foreach (array_merge(self::$idSitesView, self::$idSitesAdmin) as $idSite) {
            $result[] = array('idsite' => $idSite);
        }

        return $result;
    }

    public function execAsSuperUser($function)
    {
        $previous = self::$superUser;

        self::$superUser = true;
        try {
            $result = $function();
        } catch (\Exception $ex) {
            self::$superUser = $previous;
            throw $ex;
        }

        self::$superUser = $previous;

        return $result;
    }
}