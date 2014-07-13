<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Exception;
use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Http;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp\ServerBased;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp\Pecl;
use Piwik\Plugins\UserCountry\Reports\GetCity;
use Piwik\Plugins\UserCountry\Reports\GetContinent;
use Piwik\Plugins\UserCountry\Reports\GetCountry;
use Piwik\Plugins\UserCountry\Reports\GetRegion;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        $view = new View('@UserCountry/index');

        $view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
        $view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);

        $view->dataTableCountry = $this->renderReport(new GetCountry());
        $view->dataTableContinent = $this->renderReport(new GetContinent());
        $view->dataTableRegion = $this->renderReport(new GetRegion());
        $view->dataTableCity = $this->renderReport(new GetCity());

        return $view->render();
    }

    public function adminIndex()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        $view = new View('@UserCountry/adminIndex');

        $allProviderInfo = LocationProvider::getAllProviderInfo($newline = '<br/>', $includeExtra = true);
        $view->locationProviders = $allProviderInfo;
        $view->currentProviderId = LocationProvider::getCurrentProviderId();
        $view->thisIP = IP::getIpFromHeader();
        $geoIPDatabasesInstalled = GeoIp::isDatabaseInstalled();
        $view->geoIPDatabasesInstalled = $geoIPDatabasesInstalled;

        // check if there is a working provider (that isn't the default one)
        $isThereWorkingProvider = false;
        foreach ($allProviderInfo as $id => $provider) {
            if ($id != DefaultProvider::ID
                && $provider['status'] == LocationProvider::INSTALLED
            ) {
                $isThereWorkingProvider = true;
                break;
            }
        }
        $view->isThereWorkingProvider = $isThereWorkingProvider;

        // if using either the Apache or PECL module, they are working and there are no databases
        // in misc, then the databases are located outside of Piwik, so we cannot update them
        $view->showGeoIPUpdateSection = true;
        $currentProviderId = LocationProvider::getCurrentProviderId();
        if (!$geoIPDatabasesInstalled
            && ($currentProviderId == ServerBased::ID
                || $currentProviderId == Pecl::ID)
            && $allProviderInfo[$currentProviderId]['status'] == LocationProvider::INSTALLED
        ) {
            $view->showGeoIPUpdateSection = false;
        }

        $this->setUpdaterManageVars($view);
        $this->setBasicVariablesView($view);
        $this->setBasicVariablesAdminView($view);

        return $view->render();
    }

    /**
     * Starts or continues download of GeoLiteCity.dat.
     *
     * To avoid a server/PHP timeout & to show progress of the download to the user, we
     * use the HTTP Range header to download one chunk of the file at a time. After each
     * chunk, it is the browser's responsibility to call the method again to continue the download.
     *
     * Input:
     *   'continue' query param - if set to 1, will assume we are currently downloading & use
     *                            Range: HTTP header to get another chunk of the file.
     *
     * Output (in JSON):
     *   'current_size' - Current size of the partially downloaded file on disk.
     *   'expected_file_size' - The expected finished file size as returned by the HTTP server.
     *   'next_screen' - When the download finishes, this is the next screen that should be shown.
     *   'error' - When an error occurs, the message is returned in this property.
     */
    public function downloadFreeGeoIPDB()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();
            Json::sendHeaderJSON();
            $outputPath = GeoIp::getPathForGeoIpDatabase('GeoIPCity.dat') . '.gz';
            try {
                $result = Http::downloadChunk(
                    $url = GeoIp::GEO_LITE_URL,
                    $outputPath,
                    $continue = Common::getRequestVar('continue', true, 'int')
                );

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    GeoIPAutoUpdater::unzipDownloadedFile($outputPath, $unlink = true);

                    // setup the auto updater
                    GeoIPAutoUpdater::setUpdaterOptions(array(
                                                             'loc_db' => GeoIp::GEO_LITE_URL,
                                                             'period' => GeoIPAutoUpdater::SCHEDULE_PERIOD_MONTHLY,
                                                        ));

                    // make sure to echo out the geoip updater management screen
                    $result['next_screen'] = $this->getGeoIpUpdaterManageScreen();
                }

                return Common::json_encode($result);
            } catch (Exception $ex) {
                return Common::json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    /**
     * Renders and returns the HTML that manages the GeoIP auto-updater.
     *
     * @return string
     */
    private function getGeoIpUpdaterManageScreen()
    {
        $view = new View('@UserCountry/getGeoIpUpdaterManageScreen');
        $view->geoIPDatabasesInstalled = true;
        $this->setUpdaterManageVars($view);
        return $view->render();
    }

    /**
     * Sets some variables needed by the _updaterManage.twig template.
     *
     * @param View $view
     */
    private function setUpdaterManageVars($view)
    {
        $urls = GeoIPAutoUpdater::getConfiguredUrls();

        $view->geoIPLocUrl = $urls['loc'];
        $view->geoIPIspUrl = $urls['isp'];
        $view->geoIPOrgUrl = $urls['org'];
        $view->geoIPUpdatePeriod = GeoIPAutoUpdater::getSchedulePeriod();

        $view->geoLiteUrl = GeoIp::GEO_LITE_URL;

        $lastRunTime = GeoIPAutoUpdater::getLastRunTime();
        if ($lastRunTime !== false) {
            $view->lastTimeUpdaterRun = '<strong><em>' . $lastRunTime->toString() . '</em></strong>';
        }

        $view->nextRunTime = GeoIPAutoUpdater::getNextRunTime();
    }

    /**
     * Sets the URLs used to download new versions of the installed GeoIP databases.
     *
     * Input (query params):
     *   'loc_db' - URL for a GeoIP location database.
     *   'isp_db' - URL for a GeoIP ISP database (optional).
     *   'org_db' - URL for a GeoIP Org database (optional).
     *   'period' - 'weekly' or 'monthly'. Determines how often update is run.
     *
     * Output (json):
     *   'error' - if an error occurs its message is set as the resulting JSON object's
     *             'error' property.
     */
    public function updateGeoIPLinks()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            Json::sendHeaderJSON();
            try {
                $this->checkTokenInUrl();

                GeoIPAutoUpdater::setUpdaterOptionsFromUrl();

                // if there is a updater URL for a database, but its missing from the misc dir, tell
                // the browser so it can download it next
                $info = $this->getNextMissingDbUrlInfo();
                if ($info !== false) {
                    return Common::json_encode($info);
                } else {
                    $view = new View("@UserCountry/_updaterNextRunTime");
                    $view->nextRunTime = GeoIPAutoUpdater::getNextRunTime();
                    $nextRunTimeHtml = $view->render();
                    return Common::json_encode(array('nextRunTime' => $nextRunTimeHtml));
                }
            } catch (Exception $ex) {
                return Common::json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    /**
     * Starts or continues a download for a missing GeoIP database. A database is missing if
     * it has an update URL configured, but the actual database is not available in the misc
     * directory.
     *
     * Input:
     *   'url' - The URL to download the database from.
     *   'continue' - 1 if we're continuing a download, 0 if we're starting one.
     *
     * Output:
     *   'error' - If an error occurs this describes the error.
     *   'to_download' - The URL of a missing database that should be downloaded next (if any).
     *   'to_download_label' - The label to use w/ the progress bar that describes what we're
     *                         downloading.
     *   'current_size' - Size of the current file on disk.
     *   'expected_file_size' - Size of the completely downloaded file.
     */
    public function downloadMissingGeoIpDb()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->checkTokenInUrl();

                Json::sendHeaderJSON();

                // based on the database type (provided by the 'key' query param) determine the
                // url & output file name
                $key = Common::getRequestVar('key', null, 'string');
                $url = GeoIPAutoUpdater::getConfiguredUrl($key);

                $ext = GeoIPAutoUpdater::getGeoIPUrlExtension($url);
                $filename = GeoIp::$dbNames[$key][0] . '.' . $ext;

                if (substr($filename, 0, 15) == 'GeoLiteCity.dat') {
                    $filename = 'GeoIPCity.dat' . substr($filename, 15);
                }
                $outputPath = GeoIp::getPathForGeoIpDatabase($filename);

                // download part of the file
                $result = Http::downloadChunk(
                    $url, $outputPath, Common::getRequestVar('continue', true, 'int'));

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    GeoIPAutoUpdater::unzipDownloadedFile($outputPath, $unlink = true);

                    $info = $this->getNextMissingDbUrlInfo();
                    if ($info !== false) {
                        return Common::json_encode($info);
                    }
                }

                return Common::json_encode($result);
            } catch (Exception $ex) {
                return Common::json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    /**
     * Sets the current LocationProvider type.
     *
     * Input:
     *   Requires the 'id' query parameter to be set to the desired LocationProvider's ID.
     *
     * Output:
     *   Nothing.
     */
    public function setCurrentLocationProvider()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();

            $providerId = Common::getRequestVar('id');
            $provider = LocationProvider::setCurrentProvider($providerId);
            if ($provider === false) {
                throw new Exception("Invalid provider ID: '$providerId'.");
            }
            return 1;
        }
    }

    /**
     * Echo's a pretty formatted location using a specific LocationProvider.
     *
     * Input:
     *   The 'id' query parameter must be set to the ID of the LocationProvider to use.
     *
     * Output:
     *   The pretty formatted location that was obtained. Will be HTML.
     */
    public function getLocationUsingProvider()
    {
        $providerId = Common::getRequestVar('id');
        $provider = $provider = LocationProvider::getProviderById($providerId);
        if ($provider === false) {
            throw new Exception("Invalid provider ID: '$providerId'.");
        }

        $location = $provider->getLocation(array('ip'                => IP::getIpFromHeader(),
                                                 'lang'              => Common::getBrowserLanguage(),
                                                 'disable_fallbacks' => true));
        $location = LocationProvider::prettyFormatLocation(
            $location, $newline = '<br/>', $includeExtra = true);

        return $location;
    }

    public function getNumberOfDistinctCountries()
    {
        return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
    }

    public function getLastDistinctCountriesGraph()
    {
        $view = $this->getLastUnitGraph('UserCountry', __FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
        $view->config->columns_to_display = array('UserCountry_distinctCountries');
        return $this->renderView($view);
    }

    /**
     * Gets information for the first missing GeoIP database (if any).
     *
     * @return bool
     */
    private function getNextMissingDbUrlInfo()
    {
        $missingDbs = GeoIPAutoUpdater::getMissingDatabases();
        if (!empty($missingDbs)) {
            $missingDbKey = $missingDbs[0];
            $missingDbName = GeoIp::$dbNames[$missingDbKey][0];
            $url = GeoIPAutoUpdater::getConfiguredUrl($missingDbKey);

            $link = '<a href="' . $url . '">' . $missingDbName . '</a>';

            return array(
                'to_download'       => $missingDbKey,
                'to_download_label' => Piwik::translate('UserCountry_DownloadingDb', $link) . '...',
            );
        }
        return false;
    }

    private function dieIfGeolocationAdminIsDisabled()
    {
        if(!UserCountry::isGeoLocationAdminEnabled()) {
            throw new \Exception('Geo location setting page has been disabled.');
        }
    }
}
