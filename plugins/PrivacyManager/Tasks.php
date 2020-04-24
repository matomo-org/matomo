<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

class Tasks extends \Piwik\Plugin\Tasks
{

    /**
     * @var LogDataAnonymizations
     */
    private $logDataAnonymizations;

    /**
     * @var DataSubjects
     */
    private $dataSubjects;

    /**
     * @var SitesManagerAPI
     */
    private $sitesManagerAPI;

    public function __construct(LogDataAnonymizations $logDataAnonymizations, DataSubjects $dataSubjects, SitesManagerAPI $sitesManagerAPI)
    {
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->dataSubjects = $dataSubjects;
        $this->sitesManagerAPI = $sitesManagerAPI;
    }

    public function schedule()
    {
        $this->daily('deleteReportData', null, self::LOW_PRIORITY);
        $this->hourly('deleteLogData', null, self::LOW_PRIORITY);
        $this->hourly('anonymizePastData', null, self::LOW_PRIORITY);
        $this->weekly('deleteLogDataForDeletedSites', null, self::LOW_PRIORITY);
    }

    public function anonymizePastData()
    {
        $loop = 0;
        do {
            $loop++; // safety loop...
            $id = $this->logDataAnonymizations->getNextScheduledAnonymizationId();
            if (!empty($id)) {
                $this->logDataAnonymizations->executeScheduledEntry($id);
            }

        } while (!empty($id) && $loop < 100);
    }

    public function deleteReportData()
    {
        $privacyManager = new PrivacyManager();
        $privacyManager->deleteReportData();
    }

    /**
     * To test execute the following command:
     * `./console core:run-scheduled-tasks "Piwik\Plugins\PrivacyManager\Tasks.deleteLogData"`
     */
    public function deleteLogData()
    {
        $privacyManager = new PrivacyManager();
        $privacyManager->deleteLogData();
    }

    public function deleteLogDataForDeletedSites()
    {
        $allSiteIds = $this->sitesManagerAPI->getAllSitesId();
        $this->dataSubjects->deleteDataSubjectsForDeletedSites($allSiteIds);
    }
}
