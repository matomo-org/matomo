<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_Controller extends Piwik_Controller_Admin
{
    function index()
    {
        $view = Piwik_View::factory('index');

        $view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
        $view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);

        $view->dataTableCountry = $this->getCountry(true);
        $view->dataTableContinent = $this->getContinent(true);
        $view->dataTableRegion = $this->getRegion(true);
        $view->dataTableCity = $this->getCity(true);

        echo $view->render();
    }

    function adminIndex()
    {
        Piwik::checkUserIsSuperUser();
        $view = Piwik_View::factory('adminIndex');

        $allProviderInfo = Piwik_UserCountry_LocationProvider::getAllProviderInfo(
            $newline = '<br/>', $includeExtra = true);
        $view->locationProviders = $allProviderInfo;
        $view->currentProviderId = Piwik_UserCountry_LocationProvider::getCurrentProviderId();
        $view->thisIP = Piwik_IP::getIpFromHeader();
        $geoIPDatabasesInstalled = Piwik_UserCountry_LocationProvider_GeoIp::isDatabaseInstalled();
        $view->geoIPDatabasesInstalled = $geoIPDatabasesInstalled;

        // check if there is a working provider (that isn't the default one)
        $isThereWorkingProvider = false;
        foreach ($allProviderInfo as $id => $provider) {
            if ($id != Piwik_UserCountry_LocationProvider_Default::ID
                && $provider['status'] == Piwik_UserCountry_LocationProvider::INSTALLED
            ) {
                $isThereWorkingProvider = true;
                break;
            }
        }
        $view->isThereWorkingProvider = $isThereWorkingProvider;

        // if using either the Apache or PECL module, they are working and there are no databases
        // in misc, then the databases are located outside of Piwik, so we cannot update them
        $view->showGeoIPUpdateSection = true;
        $currentProviderId = Piwik_UserCountry_LocationProvider::getCurrentProviderId();
        if (!$geoIPDatabasesInstalled
            && ($currentProviderId == Piwik_UserCountry_LocationProvider_GeoIp_ServerBased::ID
                || $currentProviderId == Piwik_UserCountry_LocationProvider_GeoIp_Pecl::ID)
            && $allProviderInfo[$currentProviderId]['status'] == Piwik_UserCountry_LocationProvider::INSTALLED
        ) {
            $view->showGeoIPUpdateSection = false;
        }

        $this->setUpdaterManageVars($view);
        $this->setBasicVariablesView($view);
        Piwik_Controller_Admin::setBasicVariablesAdminView($view);
        $view->menu = Piwik_GetAdminMenu();

        echo $view->render();
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
        Piwik::checkUserIsSuperUser();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();
            Piwik_DataTable_Renderer_Json::sendHeaderJSON();
            $outputPath = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase('GeoIPCity.dat') . '.gz';
            try {
                $result = Piwik_Http::downloadChunk(
                    $url = Piwik_UserCountry_LocationProvider_GeoIp::GEO_LITE_URL,
                    $outputPath,
                    $continue = Piwik_Common::getRequestVar('continue', true, 'int')
                );

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    Piwik_UserCountry_GeoIPAutoUpdater::unzipDownloadedFile($outputPath, $unlink = true);

                    // setup the auto updater
                    Piwik_UserCountry_GeoIPAutoUpdater::setUpdaterOptions(array(
                                                                               'loc_db' => Piwik_UserCountry_LocationProvider_GeoIp::GEO_LITE_URL,
                                                                               'period' => Piwik_UserCountry_GeoIPAutoUpdater::SCHEDULE_PERIOD_MONTHLY,
                                                                          ));

                    // make sure to echo out the geoip updater management screen
                    $result['next_screen'] = $this->getGeoIpUpdaterManageScreen();
                }

                echo Piwik_Common::json_encode($result);
            } catch (Exception $ex) {
                echo Piwik_Common::json_encode(array('error' => $ex->getMessage()));
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
        $view = Piwik_View::factory('updaterSetup');
        $view->geoIPDatabasesInstalled = true;
        $this->setUpdaterManageVars($view);
        return $view->render();
    }

    /**
     * Sets some variables needed by the updaterSetup.tpl template.
     *
     * @param Piwik_View $view
     */
    private function setUpdaterManageVars($view)
    {
        $urls = Piwik_UserCountry_GeoIPAutoUpdater::getConfiguredUrls();

        $view->geoIPLocUrl = $urls['loc'];
        $view->geoIPIspUrl = $urls['isp'];
        $view->geoIPOrgUrl = $urls['org'];
        $view->geoIPUpdatePeriod = Piwik_UserCountry_GeoIPAutoUpdater::getSchedulePeriod();

        $view->geoLiteUrl = Piwik_UserCountry_LocationProvider_GeoIp::GEO_LITE_URL;

        $lastRunTime = Piwik_UserCountry_GeoIPAutoUpdater::getLastRunTime();
        if ($lastRunTime !== false) {
            $view->lastTimeUpdaterRun = '<strong><em>' . $lastRunTime->toString() . '</em></strong>';
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
        Piwik::checkUserIsSuperUser();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            Piwik_DataTable_Renderer_Json::sendHeaderJSON();
            try {
                $this->checkTokenInUrl();

                Piwik_UserCountry_GeoIPAutoUpdater::setUpdaterOptionsFromUrl();

                // if there is a updater URL for a database, but its missing from the misc dir, tell
                // the browser so it can download it next
                $info = $this->getNextMissingDbUrlInfo();
                if ($info !== false) {
                    echo Piwik_Common::json_encode($info);
                    return;
                }
            } catch (Exception $ex) {
                echo Piwik_Common::json_encode(array('error' => $ex->getMessage()));
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
        Piwik::checkUserIsSuperUser();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->checkTokenInUrl();

                Piwik_DataTable_Renderer_Json::sendHeaderJSON();

                // based on the database type (provided by the 'key' query param) determine the
                // url & output file name
                $key = Piwik_Common::getRequestVar('key', null, 'string');
                $url = Piwik_UserCountry_GeoIPAutoUpdater::getConfiguredUrl($key);

                $ext = Piwik_UserCountry_GeoIPAutoUpdater::getGeoIPUrlExtension($url);
                $filename = Piwik_UserCountry_LocationProvider_GeoIp::$dbNames[$key][0] . '.' . $ext;

                if (substr($filename, 0, 15) == 'GeoLiteCity.dat') {
                    $filename = 'GeoIPCity.dat' . substr($filename, 15);
                }
                $outputPath = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase($filename);

                // download part of the file
                $result = Piwik_Http::downloadChunk(
                    $url, $outputPath, Piwik_Common::getRequestVar('continue', true, 'int'));

                // if the file is done
                if ($result['current_size'] >= $result['expected_file_size']) {
                    Piwik_UserCountry_GeoIPAutoUpdater::unzipDownloadedFile($outputPath, $unlink = true);

                    $info = $this->getNextMissingDbUrlInfo();
                    if ($info !== false) {
                        echo Piwik_Common::json_encode($info);
                        return;
                    }
                }

                echo Piwik_Common::json_encode($result);
            } catch (Exception $ex) {
                echo Piwik_Common::json_encode(array('error' => $ex->getMessage()));
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
        Piwik::checkUserIsSuperUser();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();

            $providerId = Piwik_Common::getRequestVar('id');
            $provider = Piwik_UserCountry_LocationProvider::setCurrentProvider($providerId);
            if ($provider === false) {
                throw new Exception("Invalid provider ID: '$providerId'.");
            }
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
        $providerId = Piwik_Common::getRequestVar('id');
        $provider = $provider = Piwik_UserCountry_LocationProvider::getProviderById($providerId);
        if ($provider === false) {
            throw new Exception("Invalid provider ID: '$providerId'.");
        }

        $location = $provider->getLocation(array('ip'                => Piwik_IP::getIpFromHeader(),
                                                 'lang'              => Piwik_Common::getBrowserLanguage(),
                                                 'disable_fallbacks' => true));
        $location = Piwik_UserCountry_LocationProvider::prettyFormatLocation(
            $location, $newline = '<br/>', $includeExtra = true);

        echo $location;
    }

    function getCountry($fetch = false)
    {
        $view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getCountry");
        $view->setLimit(5);
        $view->setColumnTranslation('label', Piwik_Translate('UserCountry_Country'));
        $view->setReportDocumentation(Piwik_Translate('UserCountry_getCountryDocumentation'));
        return $this->renderView($view, $fetch);
    }

    function getContinent($fetch = false)
    {
        $view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getContinent", 'table');
        $view->disableSearchBox();
        $view->disableOffsetInformationAndPaginationControls();
        $view->setColumnTranslation('label', Piwik_Translate('UserCountry_Continent'));
        $view->setReportDocumentation(Piwik_Translate('UserCountry_getContinentDocumentation'));
        return $this->renderView($view, $fetch);
    }

    /**
     * Echo's or returns an HTML view of the visits by region report.
     *
     * @param bool $fetch If true, returns the HTML as a string, otherwise it is echo'd.
     * @return string
     */
    public function getRegion($fetch = false)
    {
        $view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getRegion");
        $view->setLimit(5);
        $view->setColumnTranslation('label', Piwik_Translate('UserCountry_Region'));
        $view->setReportDocumentation(Piwik_Translate('UserCountry_getRegionDocumentation') . '<br/>'
            . $this->getGeoIPReportDocSuffix());
        $this->checkIfNoDataForGeoIpReport($view);
        return $this->renderView($view, $fetch);
    }

    /**
     * Echo's or returns an HTML view of the visits by city report.
     *
     * @param bool $fetch If true, returns the HTML as a string, otherwise it is echo'd.
     * @return string
     */
    public function getCity($fetch = false)
    {
        $view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getCity");
        $view->setLimit(5);
        $view->setColumnTranslation('label', Piwik_Translate('UserCountry_City'));
        $view->setReportDocumentation(Piwik_Translate('UserCountry_getCityDocumentation') . '<br/>'
            . $this->getGeoIPReportDocSuffix());
        $this->checkIfNoDataForGeoIpReport($view);
        return $this->renderView($view, $fetch);
    }

    private function getGeoIPReportDocSuffix()
    {
        return Piwik_Translate('UserCountry_GeoIPDocumentationSuffix', array(
                                                                            '<a target="_blank" href="http://www.maxmind.com/?rId=piwik">',
                                                                            '</a>',
                                                                            '<a target="_blank" href="http://www.maxmind.com/en/city_accuracy?rId=piwik">',
                                                                            '</a>'
                                                                       ));
    }

    protected function getStandardDataTableUserCountry($currentControllerAction,
                                                       $APItoCall,
                                                       $defaultDatatableType = null)
    {
        $view = Piwik_ViewDataTable::factory($defaultDatatableType);
        $view->init($this->pluginName, $currentControllerAction, $APItoCall);
        $view->disableExcludeLowPopulation();

        $this->setPeriodVariablesView($view);
        $this->setMetricsVariablesView($view);

        $view->enableShowGoals();

        return $view;
    }

    function getNumberOfDistinctCountries($fetch = false)
    {
        return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
    }

    function getLastDistinctCountriesGraph($fetch = false)
    {
        $view = $this->getLastUnitGraph('UserCountry', __FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
        $view->setColumnsToDisplay('UserCountry_distinctCountries');
        return $this->renderView($view, $fetch);
    }

    /**
     * Checks if a datatable for a view is empty and if so, displays a message in the footer
     * telling users to configure GeoIP.
     */
    private function checkIfNoDataForGeoIpReport($view)
    {
        // only display on HTML tables since the datatable for HTML graphs aren't accessible
        if (!($view instanceof Piwik_ViewDataTable_HtmlTable)) {
            return;
        }

        // if there's only one row whose label is 'Unknown', display a message saying there's no data
        $view->main();
        $dataTable = $view->getDataTable();
        if ($dataTable->getRowsCount() == 1
            && $dataTable->getFirstRow()->getColumn('label') == Piwik_Translate('General_Unknown')
        ) {
            $footerMessage = Piwik_Translate('UserCountry_NoDataForGeoIPReport1');

            // if GeoIP is working, don't display this part of the message
            if (!$this->isGeoIPWorking()) {
                $params = array('module' => 'UserCountry', 'action' => 'adminIndex');
                $footerMessage .= ' ' . Piwik_Translate('UserCountry_NoDataForGeoIPReport2', array(
                                                                                                  '<a target="_blank" href="' . Piwik_Url::getCurrentQueryStringWithParametersModified($params) . '">',
                                                                                                  '</a>',
                                                                                                  '<a target="_blank" href="http://dev.maxmind.com/geoip/geolite?rId=piwik">',
                                                                                                  '</a>'
                                                                                             ));
            } else {
                $footerMessage .= ' ' . Piwik_Translate('UserCountry_ToGeolocateOldVisits', array(
                                                                                                 '<a target="_blank" href="http://piwik.org/faq/how-to/#faq_167">',
                                                                                                 '</a>'
                                                                                            ));
            }

            // HACK! Can't use setFooterMessage because the view gets built in the main function,
            // so instead we set the property by hand.
            $realView = $view->getView();
            $properties = $realView->properties;
            $properties['show_footer_message'] = $footerMessage;
            $realView->properties = $properties;
        }
    }

    /**
     * Gets information for the first missing GeoIP database (if any).
     *
     * @return bool
     */
    private function getNextMissingDbUrlInfo()
    {
        $missingDbs = Piwik_UserCountry_GeoIPAutoUpdater::getMissingDatabases();
        if (!empty($missingDbs)) {
            $missingDbKey = $missingDbs[0];
            $missingDbName = Piwik_UserCountry_LocationProvider_GeoIp::$dbNames[$missingDbKey][0];
            $url = Piwik_UserCountry_GeoIPAutoUpdater::getConfiguredUrl($missingDbKey);

            $link = '<a href="' . $url . '">' . $missingDbName . '</a>';

            return array(
                'to_download'       => $missingDbKey,
                'to_download_label' => Piwik_Translate('UserCountry_DownloadingDb', $link) . '...',
            );
        }
        return false;
    }

    /**
     * Returns true if a GeoIP provider is installed & working, false if otherwise.
     *
     * @return bool
     */
    private function isGeoIPWorking()
    {
        $provider = Piwik_UserCountry_LocationProvider::getCurrentProvider();
        return $provider instanceof Piwik_UserCountry_LocationProvider_GeoIp
            && $provider->isAvailable() === true
            && $provider->isWorking() === true;
    }
}
