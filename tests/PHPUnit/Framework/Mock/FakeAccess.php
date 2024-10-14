<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

use Piwik\Access;
use Piwik\Auth;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Site as PiwikSite;

/**
 * FakeAccess for UnitTests
 * @since 2.8.0
 */
class FakeAccess extends Access
{
    public static $superUser = false;
    public static $idSitesAdmin = array();
    public static $idSitesWrite = array();
    public static $idSitesView = array();
    public static $idSitesCapabilities = array();
    public static $identity = 'superUserLogin';
    public static $superUserLogin = 'superUserLogin';

    public static function clearAccess($superUser = false, $idSitesAdmin = array(), $idSitesView = array(), $identity = 'superUserLogin', $idSitesWrite = array(), $idSitesCapabilities = array())
    {
        self::$superUser    = $superUser;
        self::$idSitesAdmin = $idSitesAdmin;
        self::$idSitesWrite = $idSitesWrite;
        self::$idSitesView  = $idSitesView;
        self::$identity     = $identity;
        self::$idSitesCapabilities = $idSitesCapabilities;
    }

    public function getTokenAuth()
    {
        return false;
    }

    public function __construct($superUser = false, $idSitesAdmin = array(), $idSitesView = array(), $identity = 'superUserLogin', $idSitesWrite = array())
    {
        // couldn't use DI here as tests seem to fail cause at this time when it is called eg in
        // plugins/Live/tests/System/ApiCounterTest.php the environment is not set up yet
        $role = new Access\RolesProvider();
        $capabilities = new Access\CapabilitiesProvider();
        parent::__construct($role, $capabilities);

        self::clearAccess($superUser, $idSitesAdmin, $idSitesView, $identity, $idSitesWrite);
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

    public static function setIdSitesWrite($ids)
    {
        self::$superUser    = false;
        self::$idSitesWrite = $ids;
    }

    public function hasSuperUserAccess()
    {
        return self::$superUser;
    }

    public function checkUserHasSuperUserAccess()
    {
        if (!self::$superUser) {
            throw new NoAccessException("checkUserHasSuperUserAccess Fake exception // string not to be tested");
        }
    }

    public function setSuperUserAccess($bool = true)
    {
        self::$superUser = $bool;
    }

    public function reloadAccess(?Auth $auth = null)
    {
        return true;
    }

    public function checkUserHasAdminAccess($idSites)
    {
        if (!self::$superUser) {
            $websitesAccess = self::$idSitesAdmin;
        } else {
            return;
        }

        $idSites = PiwikSite::getIdSitesFromIdSitesString($idSites);

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new NoAccessException("checkUserHasAdminAccess Fake exception // string not to be tested");
            }
        }
    }

    public function checkUserHasWriteAccess($idSites)
    {
        if (!self::$superUser) {
            $websitesAccess = array_merge(self::$idSitesWrite, self::$idSitesAdmin);
        } else {
            return;
        }

        $idSites = PiwikSite::getIdSitesFromIdSitesString($idSites);

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $websitesAccess)) {
                throw new NoAccessException("checkUserHasWriteAccess Fake exception // string not to be tested");
            }
        }
    }

    public function checkUserHasCapability($idSites, $capability)
    {
        $cap = $this->capabilityProvider->getCapability($capability);

        if ($cap && Piwik::isUserHasAdminAccess($idSites) && $cap->hasRoleCapability(Access\Role\Admin::ID)) {
            return;
        } elseif ($cap && Piwik::isUserHasWriteAccess($idSites) && $cap->hasRoleCapability(Access\Role\Write::ID)) {
            return;
        } elseif ($cap && Piwik::isUserHasViewAccess($idSites) && $cap->hasRoleCapability(Access\Role\View::ID)) {
            return;
        }

        if (isset(self::$idSitesCapabilities[$capability]) && is_array(self::$idSitesCapabilities[$capability])) {
            if (!is_array($idSites)) {
                $idSites = array($idSites);
            }
            $idSites = array_map('intval', $idSites);
            $idSitesCap = array_map('intval', self::$idSitesCapabilities[$capability]);
            $missingSites = array_diff($idSites, $idSitesCap);
            if (empty($missingSites)) {
                return;
            }
        }

        throw new NoAccessException("checkUserHasCapability " . $capability . " Fake exception // string not to be tested");
    }

    //means at least view access
    public function checkUserHasViewAccess($idSites)
    {
        if (self::$superUser) {
            return;
        }

        $websitesAccess = array_merge(self::$idSitesView, self::$idSitesWrite, self::$idSitesAdmin);

        if (!is_array($idSites)) {
            if ($idSites === 'all') {
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

    public function checkUserHasSomeViewAccess()
    {
        if (!self::$superUser) {
            if (count(array_merge(self::$idSitesView, self::$idSitesWrite, self::$idSitesAdmin)) == 0) {
                throw new NoAccessException("checkUserHasSomeViewAccess Fake exception // string not to be tested");
            }
        } else {
            return;
        }
    }

    //means at least admin access
    public function isUserHasSomeAdminAccess()
    {
        if (self::$superUser) {
            return true;
        }

        return count(self::$idSitesAdmin) > 0;
    }

    //means at least write access
    public function isUserHasSomeWriteAccess()
    {
        if (self::$superUser) {
            return true;
        }

        return count(self::$idSitesAdmin) > 0 || count(self::$idSitesWrite) > 0;
    }

    //means at least admin access
    public function checkUserHasSomeAdminAccess()
    {
        if (!$this->isUserHasSomeAdminAccess()) {
            throw new NoAccessException("checkUserHasSomeAdminAccess Fake exception // string not to be tested");
        }
    }

    //means at least write access
    public function checkUserHasSomeWriteAccess()
    {
        if (!$this->isUserHasSomeWriteAccess()) {
            throw new NoAccessException("checkUserHasSomeWriteAccess Fake exception // string not to be tested");
        }
    }

    public function getLogin()
    {
        return self::$identity;
    }

    public function getSitesIdWithAdminAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return self::$idSitesAdmin;
    }

    public function getSitesIdWithViewAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return self::$idSitesView;
    }

    public function getSitesIdWithWriteAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return self::$idSitesWrite;
    }

    public function getSitesIdWithAtLeastViewAccess()
    {
        if (self::$superUser) {
            return API::getInstance()->getAllSitesId();
        }

        return array_merge(self::$idSitesView, self::$idSitesWrite, self::$idSitesAdmin);
    }

    public function getRawSitesWithSomeViewAccess($login)
    {
        $result = array();

        foreach (array_merge(self::$idSitesView, self::$idSitesWrite, self::$idSitesAdmin) as $idSite) {
            $result[] = array('idsite' => $idSite);
        }

        return $result;
    }
}
