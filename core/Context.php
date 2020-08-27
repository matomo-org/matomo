<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

/**
 * Methods related to changing the context Matomo code is running in.
 */
class Context
{
    public static function executeWithQueryParameters(array $parametersRequest, callable $callback)
    {
        // Temporarily sets the Request array to this API call context
        $saveGET = $_GET;
        $savePOST = $_POST;
        $saveQUERY_STRING = @$_SERVER['QUERY_STRING'];
        foreach ($parametersRequest as $param => $value) {
            $_GET[$param] = $value;
            $_POST[$param] = $value;
        }

        try {
            return $callback();
        } finally {
            $_GET = $saveGET;
            $_POST = $savePOST;
            $_SERVER['QUERY_STRING'] = $saveQUERY_STRING;
        }
    }

    /**
     * Temporarily overwrites the idSite parameter so all code executed by `$callback()`
     * will use that idSite.
     *
     * Useful when you need to change the idSite context for a chunk of code. For example,
     * if we are archiving for more than one site in sequence, we don't want to use
     * the same caches for both archiving executions.
     *
     * @param string|int $idSite
     * @param callable $callback
     * @return mixed returns result of $callback
     */
    public static function changeIdSite($idSite, $callback)
    {
        // temporarily set the idSite query parameter so archiving will end up using
        // the correct site aware caches
        $originalGetIdSite = isset($_GET['idSite']) ? $_GET['idSite'] : null;
        $originalPostIdSite = isset($_POST['idSite']) ? $_POST['idSite'] : null;

        $originalGetIdSites = isset($_GET['idSites']) ? $_GET['idSites'] : null;
        $originalPostIdSites = isset($_POST['idSites']) ? $_POST['idSites'] : null;

        $originalTrackerGetIdSite = isset($_GET['idsite']) ? $_GET['idsite'] : null;
        $originalTrackerPostIdSite = isset($_POST['idsite']) ? $_POST['idsite'] : null;

        try {
            $_GET['idSite'] = $_POST['idSite'] = $idSite;

            if (Tracker::$initTrackerMode) {
                $_GET['idsite'] = $_POST['idsite'] = $idSite;
            }

            // idSites is a deprecated query param that is still in use. since it is deprecated and new
            // supported code shouldn't rely on it, we can (more) safely unset it here, since we are just
            // calling downstream matomo code. we unset it because we don't want it interfering w/
            // code in $callback().
            unset($_GET['idSites']);
            unset($_POST['idSites']);

            return $callback();
        } finally {
            self::resetIdSiteParam($_GET, 'idSite', $originalGetIdSite);
            self::resetIdSiteParam($_POST, 'idSite', $originalPostIdSite);
            self::resetIdSiteParam($_GET, 'idSites', $originalGetIdSites);
            self::resetIdSiteParam($_POST, 'idSites', $originalPostIdSites);
            self::resetIdSiteParam($_GET, 'idsite', $originalTrackerGetIdSite);
            self::resetIdSiteParam($_POST, 'idsite', $originalTrackerPostIdSite);
        }
    }

    private static function resetIdSiteParam(&$superGlobal, $paramName, $originalValue)
    {
        if ($originalValue !== null) {
            $superGlobal[$paramName] = $originalValue;
        } else {
            unset($superGlobal[$paramName]);
        }
    }
}