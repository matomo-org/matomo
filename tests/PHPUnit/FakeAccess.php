<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\SitesManager\API;
use Piwik\Site;

/**
 * FakeAccess for UnitTests
 */
class FakeAccess
{
    public static $superUser = false;
    public static $idSitesAdmin = array();
    public static $idSitesView = array();
    public static $identity = 'superUserLogin';
    public static $superUserLogin = 'superUserLogin';

    public function getTokenAuth()
    {
        return false;
    }

    public function __construct()
    {
        self::$superUser = false;
        self::$idSitesAdmin = array();
        self::$idSitesView = array();
        self::$identity = 'superUserLogin';
    }

    public static function setIdSitesAdmin($ids)
    {
        self::$superUser = false;
        self::$idSitesAdmin = $ids;
    }

    public static function setIdSitesView($ids)
    {
        self::$superUser = false;
        self::$idSitesView = $ids;
    }

    public static function checkUserIsSuperUser()
    {
        if (!self::$superUser) {
            throw new Exception("checkUserIsSuperUser Fake exception // string not to be tested");
        }
    }

    public static function setSuperUser($bool = true)
    {
        self::$superUser = $bool;
    }

    public static function reloadAccess()
    {
    }

    public static function checkUserHasAdminAccess($idSites)
    {
        if (!self::$superUser) {
            $websitesAccess = self::$idSitesAdmin;
        } else {
            $websitesAccess = API::getInstance()->getAllSitesId();
        }

        $idSites = Site::getIdSitesFromIdSitesString($idSites);
        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new Exception("checkUserHasAdminAccess Fake exception // string not to be tested");
            }
        }
    }

    //means at least view access
    public static function checkUserHasViewAccess($idSites)
    {
        if (!self::$superUser) {
            $websitesAccess = array_merge(self::$idSitesView, self::$idSitesAdmin);
        } else {
            $websitesAccess = API::getInstance()->getAllSitesId();
        }

        if (!is_array($idSites)) {
            if ($idSites == 'all') {
                $idSites = API::getInstance()->getAllSitesId();
            } else {
                $idSites = explode(',', $idSites);
            }
        }

        if (empty($websitesAccess)) {
            throw new Exception("checkUserHasViewAccess Fake exception // string not to be tested");
        }

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new Exception("checkUserHasViewAccess Fake exception // string not to be tested");
            }
        }
    }

    public static function checkUserHasSomeViewAccess()
    {
        if (!self::$superUser) {
            if (count(self::$idSitesView) == 0) {
                throw new Exception("checkUserHasSomeViewAccess Fake exception // string not to be tested");
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
                throw new Exception("checkUserHasSomeAdminAccess Fake exception // string not to be tested");
            }
        } else {
            return; //super user has some admin rights
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
    
    public function getSuperUserLogin()
    {
        return self::$superUserLogin;
    }
}
