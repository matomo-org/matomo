<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;

class Tasks extends \Piwik\Plugin\Tasks
{

    /**
     * @var LogDataAnonymizations
     */
    private $logDataAnonymizations;

    public function __construct(LogDataAnonymizations $logDataAnonymizations)
    {
        $this->logDataAnonymizations = $logDataAnonymizations;
    }

    public function schedule()
    {
        $this->daily('deleteReportData', null, self::LOW_PRIORITY);
        $this->daily('deleteLogData', null, self::LOW_PRIORITY);
        $this->hourly('anonymizePastData', null, self::LOW_PRIORITY);
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

    public function deleteLogData()
    {
        $privacyManager = new PrivacyManager();
        $privacyManager->deleteLogData();
    }
}