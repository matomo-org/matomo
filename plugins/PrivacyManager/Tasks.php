<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Plugins\PrivacyManager\Model\ScheduledLogDataAnonymization;

class Tasks extends \Piwik\Plugin\Tasks
{

    /**
     * @var ScheduledLogDataAnonymization
     */
    private $scheduledLogDataAnonymization;

    public function __construct(ScheduledLogDataAnonymization $scheduledLogDataAnonymization)
    {
        $this->scheduledLogDataAnonymization = $scheduledLogDataAnonymization;
    }

    public function schedule()
    {
        $this->daily('deleteReportData', null, self::LOW_PRIORITY);
        $this->daily('deleteLogData', null, self::LOW_PRIORITY);
        $this->hourly('anonymizePastData', null, self::LOW_PRIORITY);
    }

    public function anonymizePastData()
    {
        $schedules = $this->scheduledLogDataAnonymization->getAllEntries();

        foreach ($schedules as $index => $schedule) {
            if (empty($schedule['isStarted']) && empty($schedule['isFinished'])) {
                // during one task run we want to start executing max one entry because this may take a lot of time.
                // this also simplifies logic here to not having to run in a do/while loop and re-fetching getAllSchedules()
                // after executing this entry etc.
                $this->scheduledLogDataAnonymization->executeScheduledEntry($index);
                return;
            }
        }
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