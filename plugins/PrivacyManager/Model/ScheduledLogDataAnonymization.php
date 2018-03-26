<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager\Model;

use Piwik\Date;
use Piwik\Option;
use Piwik\Period\Factory as PeriodFactory;

class ScheduledLogDataAnonymization
{
    const LOG_DATA_ANONYMIZATION = "PrivacyManager.logDataAnonymization";

    /**
     * @var null|callable
     */
    private $onOutputCallback;

    /**
     * @var LogDataAnonymizer
     */
    private $logDataAnonymizer;

    public function __construct(LogDataAnonymizer $logDataAnonymizer)
    {
        $this->logDataAnonymizer = $logDataAnonymizer;
    }

    public function getAllEntries()
    {
        Option::clearCachedOption(self::LOG_DATA_ANONYMIZATION); // we make sure to always fetch latest entry
        $optionData = Option::get(self::LOG_DATA_ANONYMIZATION);
        $table = @json_decode($optionData, true);

        if (empty($table)) {
            return array();
        }

        return $table;
    }

    public function scheduleEntry($requester, $idSites, $date, $anonymizeIp, $anonymizeLocation, $unsetVisitColumns, $unsetLinkVisitActionColumns, $isStarted = false)
    {
        if (empty($date)) {
            throw new \Exception('No date specified');
        }
        list($startDate, $endDate) = $this->getStartAndEndDate($date); // make sure valid date
        if (!empty($unsetVisitColumns)) {
            $this->logDataAnonymizer->checkAllVisitColumns($unsetVisitColumns);
        } else {
            $unsetVisitColumns = array();
        }
        if (!empty($unsetLinkVisitActionColumns)) {
            $this->logDataAnonymizer->checkAllLinkVisitActionColumns($unsetLinkVisitActionColumns);
        } else {
            $unsetLinkVisitActionColumns = array();
        }
        if (!$anonymizeIp && !$anonymizeLocation && empty($unsetVisitColumns) && empty($unsetLinkVisitActionColumns)) {
            throw new \Exception('Nothing is selected to be anonymized');
        }

        $schedules = $this->getAllEntries();
        $schedules[] = array(
            'idsites' => $idSites,
            'date' => $date,
            'anonymizeIp' => !empty($anonymizeIp) ? true : false,
            'anonymizeLocation' => !empty($anonymizeLocation) ? true : false,
            'unsetVisitColumns' => $unsetVisitColumns,
            'unsetLinkVisitActionColumns' => $unsetLinkVisitActionColumns,
            'output' => '',
            'scheduledOn' => Date::now()->getDatetime(),
            'isStarted' => $isStarted,
            'startDate' => null,
            'isFinished' => false,
            'finishDate' => null,
            'requester' => $requester,
        );
        $this->setSchedules($schedules);
        return count($schedules) - 1;
    }

    private function updateEntryAtIndex($index, $field, $value)
    {
        $schedules = $this->getAllEntries();
        if (isset($schedules[$index])) {
            $schedules[$index][$field] = $value;
            $this->setSchedules($schedules);
        }
    }

    public function setSchedules($schedules)
    {
        Option::set(self::LOG_DATA_ANONYMIZATION, json_encode($schedules));
    }

    public function setCallbackOnOutput($callback)
    {
        $this->onOutputCallback = $callback;
    }

    private function appendToOutput($index, &$schedule, $message)
    {
        $schedule['output'] .= $message . "\n";
        $this->updateEntryAtIndex($index, 'output', $schedule['output']);

        if ($this->onOutputCallback && is_callable($this->onOutputCallback)) {
            call_user_func($this->onOutputCallback, $message);
        }
    }

    public function getStartAndEndDate($date)
    {
        if (strpos($date, ',') === false) {
            $period = PeriodFactory::build('day', $date);
        } else {
            $period = PeriodFactory::build('range', $date);
        }
        $startDate = $period->getDateTimeStart()->getDatetime();
        $endDate = $period->getDateTimeEnd()->getDatetime();
        return array($startDate, $endDate);
    }

    public function executeScheduledEntry($index)
    {
        $schedules = $this->getAllEntries();
        $schedule = $schedules[$index];

        $this->updateEntryAtIndex($index, 'isStarted', true);
        $this->updateEntryAtIndex($index, 'startDate', Date::now()->getDatetime());

        $logDataAnonymizer = new LogDataAnonymizer();

        $idSites = $schedule['idsites'];

        list($startDate, $endDate) = $this->getStartAndEndDate($schedule['date']);

        if (empty($idSites)) {
            $idSites = null;
            $this->appendToOutput($index, $schedule, "Running behaviour on all sites.");
        } else {
            $this->appendToOutput($index, $schedule, 'Running behaviour on these sites: ' . implode(', ', $idSites));
        }

        $this->appendToOutput($index, $schedule, sprintf("Applying this to visits between '%s' and '%s'.", $startDate, $endDate));

        if ($schedule['anonymizeIp'] || $schedule['anonymizeLocation']) {
            $this->appendToOutput($index, $schedule, 'Starting to anonymize visit information.');
            try {
                $numAnonymized = $logDataAnonymizer->anonymizeVisitInformation($idSites, $startDate, $endDate, $schedule['anonymizeIp'], $schedule['anonymizeLocation']);
                $this->appendToOutput($index, $schedule, 'Number of anonymized IP and/or location: ' . $numAnonymized);
            } catch (\Exception $e) {
                $this->appendToOutput($index, $schedule, 'Failed to anonymize IP and/or location:' . $e->getMessage());
            }
        }

        if (!empty($schedule['unsetVisitColumns'])) {
            try {
                $this->appendToOutput($index, $schedule, 'Starting to unset log_visit table entries.');
                $numColumnsUnset = $logDataAnonymizer->unsetLogVisitTableColumns($idSites, $startDate, $endDate, $schedule['unsetVisitColumns']);
                $this->appendToOutput($index, $schedule, 'Number of unset log_visit table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($index, $schedule, 'Failed to unset log_visit table entries:' . $e->getMessage());
            }

            try {
                $this->appendToOutput($index, $schedule, 'Starting to unset log_conversion table entries (if possible).');
                $numColumnsUnset = $logDataAnonymizer->unsetLogConversionTableColumns($idSites, $startDate, $endDate, $schedule['unsetVisitColumns']);
                $this->appendToOutput($index, $schedule, 'Number of unset log_conversion table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($index, $schedule, 'Failed to unset log_conversion table entries:' . $e->getMessage());
            }
        }

        if (!empty($schedule['unsetLinkVisitActionColumns'])) {
            try {
                $this->appendToOutput($index, $schedule, 'Starting to unset log_link_visit_action table entries.');
                $numColumnsUnset = $logDataAnonymizer->unsetLogLinkVisitActionColumns($idSites, $startDate, $endDate, $schedule['unsetLinkVisitActionColumns']);
                $this->appendToOutput($index, $schedule, 'Number of unset log_link_visit_action table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($index, $schedule, 'Failed to unset log_link_visit_action table entries:' . $e->getMessage());
            }
        }

        $this->updateEntryAtIndex($index, 'isFinished', true);
        $this->updateEntryAtIndex($index, 'finishDate', Date::now()->getDatetime());
    }

}
