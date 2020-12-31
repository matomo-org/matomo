<?php
namespace Piwik\Plugins\GeoIp2;

use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\UserCountry;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /**
     * Starts or continues download of DBIP-City.mmdb.
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
    public function downloadFreeDBIPLiteDB()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();
            Json::sendHeaderJSON();
            $outputPath = GeoIP2AutoUpdater::getTemporaryFolder('DBIP-City.mmdb.gz', true);
            try {
                $result = Http::downloadChunk(
                    $url = GeoIp2::getDbIpLiteUrl(),
                    $outputPath,
                    $continue = Common::getRequestVar('continue', true, 'int')
                );

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    try {
                        GeoIP2AutoUpdater::unzipDownloadedFile($outputPath, 'loc', $url, $unlink = true);
                    } catch (\Exception $e) {
                        // remove downloaded file on error
                        unlink($outputPath);
                        throw $e;
                    }

                    // setup the auto updater
                    GeoIP2AutoUpdater::setUpdaterOptions(array(
                        'loc' => GeoIp2::getDbIpLiteUrl(),
                        'period' => GeoIP2AutoUpdater::SCHEDULE_PERIOD_MONTHLY,
                    ));

                    $result['settings'] = GeoIP2AutoUpdater::getConfiguredUrls();
                }

                return json_encode($result);
            } catch (\Exception $ex) {
                return json_encode(array('error' => $ex->getMessage()));
            }
        }
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

                GeoIP2AutoUpdater::setUpdaterOptionsFromUrl();

                // if there is a updater URL for a database, but its missing from the misc dir, tell
                // the browser so it can download it next
                $info = $this->getNextMissingDbUrlInfoGeoIp2();

                if ($info !== false) {
                    return json_encode($info);
                } else {
                    $view = new View("@GeoIp2/_updaterNextRunTime");
                    $view->nextRunTime = GeoIP2AutoUpdater::getNextRunTime();
                    $nextRunTimeHtml = $view->render();
                    return json_encode(array('nextRunTime' => $nextRunTimeHtml));
                }
            } catch (\Exception $ex) {
                return json_encode(array('error' => $ex->getMessage()));
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

                $url = GeoIP2AutoUpdater::getConfiguredUrl($key);
                $filename = GeoIP2AutoUpdater::getZippedFilenameToDownloadTo($url, $key, GeoIP2AutoUpdater::getGeoIPUrlExtension($url));
                $outputPath = GeoIP2AutoUpdater::getTemporaryFolder($filename, true);

                // download part of the file
                $result = Http::downloadChunk(
                    $url, $outputPath, Common::getRequestVar('continue', true, 'int'));

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    GeoIP2AutoUpdater::unzipDownloadedFile($outputPath, $key, $url, $unlink = true);

                    $info = $this->getNextMissingDbUrlInfoGeoIp2();
                    if ($info !== false) {
                        return json_encode($info);
                    }
                }

                return json_encode($result);
            } catch (\Exception $ex) {
                return json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    /**
     * Gets information for the first missing GeoIP2 database (if any).
     *
     * @return array|bool
     */
    private function getNextMissingDbUrlInfoGeoIp2()
    {
        $missingDbs = GeoIP2AutoUpdater::getMissingDatabases();
        if (!empty($missingDbs)) {
            $missingDbKey = $missingDbs[0];
            $url = GeoIP2AutoUpdater::getConfiguredUrl($missingDbKey);

            $link = '<a href="' . $url . '">' . $url . '</a>';

            return [
                'to_download'       => $missingDbKey,
                'to_download_label' => Piwik::translate('GeoIp2_DownloadingDb', $link) . '...',
            ];
        }
        return false;
    }

    private function dieIfGeolocationAdminIsDisabled()
    {
        if (!UserCountry::isGeoLocationAdminEnabled()) {
            throw new \Exception('Geo location setting page has been disabled.');
        }
    }
}