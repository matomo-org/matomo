<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Piwik;
use Piwik\Config as PiwikConfig;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Plugins\PrivacyManager\Validators\VisitsDataSubject;
use Piwik\Site;
use Piwik\Validators\BaseValidator;

/**
 * API for plugin PrivacyManager
 *
 * @method static \Piwik\Plugins\PrivacyManager\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var DataSubjects
     */
    private $gdpr;

    /**
     * @var LogDataAnonymizations
     */
    private $logDataAnonymizations;

    /**
     * @var LogDataAnonymizer
     */
    private $logDataAnonymizer;

    public function __construct(DataSubjects $gdpr, LogDataAnonymizations $logDataAnonymizations, LogDataAnonymizer $logDataAnonymizer)
    {
        $this->gdpr = $gdpr;
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->logDataAnonymizer = $logDataAnonymizer;
    }

    private function checkDataSubjectVisits($visits)
    {
        BaseValidator::check('visits', $visits, [new VisitsDataSubject()]);

        $idSites = array();
        foreach ($visits as $index => $visit) {
            $idSites[] = $visit['idsite'];
        }
        Piwik::checkUserHasAdminAccess($idSites);
    }

    public function deleteDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->deleteDataSubjects($visits);
    }

    public function exportDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->exportDataSubjects($visits);
    }

    public function anonymizeSomeRawData($idSites, $date, $anonymizeIp = false, $anonymizeLocation = false, $anonymizeUserId = false, $unsetVisitColumns = [], $unsetLinkVisitActionColumns = [])
    {
        Piwik::checkUserHasSuperUserAccess();

        if ($idSites === 'all' || empty($idSites)) {
            $idSites = null; // all websites
        } else {
            $idSites = Site::getIdSitesFromIdSitesString($idSites);
        }
        $requester = Piwik::getCurrentUserLogin();
        $this->logDataAnonymizations->scheduleEntry($requester, $idSites, $date, $anonymizeIp, $anonymizeLocation, $anonymizeUserId, $unsetVisitColumns, $unsetLinkVisitActionColumns);
    }

    public function getAvailableVisitColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableVisitColumnsToAnonymize();
        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    public function getAvailableLinkVisitActionColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableLinkVisitActionColumnsToAnonymize();
        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    private function formatAvailableColumnsToAnonymize($columns)
    {
        ksort($columns);
        $formatted = array();
        foreach ($columns as $column => $default) {
            $formatted[] = array(
                'column_name' => $column,
                'default_value' => $default
            );
        }
        return $formatted;
    }

    /**
     * @internal
     */
    public function setAnonymizeIpSettings($anonymizeIPEnable, $maskLength, $useAnonymizedIpForVisitEnrichment, $anonymizeUserId = false, $anonymizeOrderId = false)
    {
        Piwik::checkUserHasSuperUserAccess();

        if ($anonymizeIPEnable == '1') {
            IPAnonymizer::activate();
        } else if ($anonymizeIPEnable == '0') {
            IPAnonymizer::deactivate();
        } else {
            // pass
        }

        $privacyConfig = new Config();
        $privacyConfig->ipAddressMaskLength = (int) $maskLength;
        $privacyConfig->useAnonymizedIpForVisitEnrichment = (bool) $useAnonymizedIpForVisitEnrichment;

        if (false !== $anonymizeUserId) {
            $privacyConfig->anonymizeUserId = (bool) $anonymizeUserId;
        }

        if (false !== $anonymizeOrderId) {
            $privacyConfig->anonymizeOrderId = (bool) $anonymizeOrderId;
        }

        return true;
    }

    /**
     * @internal
     */
    public function deactivateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->deactivate();

        return true;
    }

    /**
     * @internal
     */
    public function activateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->activate();

        return true;
    }

    /**
     * @internal
     */
    public function setScheduleReportDeletionSettings($deleteLowestInterval = 7)
    {
        return $this->savePurgeDataSettings(array(
            'delete_logs_schedule_lowest_interval' => (int) $deleteLowestInterval
        ));
    }

    /**
     * @internal
     */
    public function setDeleteLogsSettings($enableDeleteLogs = '0', $deleteLogsOlderThan = 180)
    {
        $deleteLogsOlderThan = (int) $deleteLogsOlderThan;
        if ($deleteLogsOlderThan < 1) {
            $deleteLogsOlderThan = 1;
        }

        return $this->savePurgeDataSettings(array(
            'delete_logs_enable' => !empty($enableDeleteLogs),
            'delete_logs_older_than' => $deleteLogsOlderThan,
        ));
    }

    /**
     * @internal
     */
    public function setDeleteReportsSettings($enableDeleteReports = 0, $deleteReportsOlderThan = 3,
                                             $keepBasic = 0, $keepDay = 0, $keepWeek = 0, $keepMonth = 0,
                                             $keepYear = 0, $keepRange = 0, $keepSegments = 0)
    {
        $settings = array();

        // delete reports settings
        $settings['delete_reports_enable'] = !empty($enableDeleteReports);

        $deleteReportsOlderThan = (int) $deleteReportsOlderThan;
        if ($deleteReportsOlderThan < 2) {
            $deleteReportsOlderThan = 2;
        }

        $settings['delete_reports_older_than'] = $deleteReportsOlderThan;

        $settings['delete_reports_keep_basic_metrics']   = (int) $keepBasic;
        $settings['delete_reports_keep_day_reports']     = (int) $keepDay;
        $settings['delete_reports_keep_week_reports']    = (int) $keepWeek;
        $settings['delete_reports_keep_month_reports']   = (int) $keepMonth;
        $settings['delete_reports_keep_year_reports']    = (int) $keepYear;
        $settings['delete_reports_keep_range_reports']   = (int) $keepRange;
        $settings['delete_reports_keep_segment_reports'] = (int) $keepSegments;
        $settings['delete_logs_max_rows_per_query']      = PiwikConfig::getInstance()->Deletelogs['delete_logs_max_rows_per_query'];

        return $this->savePurgeDataSettings($settings);
    }

    private function savePurgeDataSettings($settings)
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkDataPurgeAdminSettingsIsEnabled();

        PrivacyManager::savePurgeDataSettings($settings);

        return true;
    }
    
    private function checkDataPurgeAdminSettingsIsEnabled()
    {
        if (!Controller::isDataPurgeSettingsEnabled()) {
            throw new \Exception("Configuring deleting raw data and report data has been disabled by Matomo admins.");
        }
    }
}
