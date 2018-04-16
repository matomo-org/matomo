<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager\Model;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Site;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

class LogDataAnonymizations
{
    /**
     * @var null|callable
     */
    private $onOutputCallback;

    /**
     * @var LogDataAnonymizer
     */
    private $logDataAnonymizer;

    private $tablePrefixed;

    public function __construct(LogDataAnonymizer $logDataAnonymizer)
    {
        $this->logDataAnonymizer = $logDataAnonymizer;
        $this->tablePrefixed = Common::prefixTable(self::getDbTableName());
    }

    public static function getDbTableName()
    {
        return 'privacy_logdata_anonymizations';
    }

    public function install()
    {
        DbHelper::createTable(self::getDbTableName(), "
                  `idlogdata_anonymization` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `idsites` TEXT NULL DEFAULT NULL,
                  `date_start` DATETIME NOT NULL,
                  `date_end` DATETIME NOT NULL,
                  `anonymize_ip` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  `anonymize_location` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  `anonymize_userid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  `unset_visit_columns` TEXT NOT NULL DEFAULT '',
                  `unset_link_visit_action_columns` TEXT NOT NULL DEFAULT '',
                  `output` MEDIUMTEXT NULL DEFAULT NULL,
                  `scheduled_date` DATETIME NULL,
                  `job_start_date` DATETIME NULL,
                  `job_finish_date` DATETIME NULL,
                  `requester` VARCHAR(100) NOT NULL DEFAULT '',
                  PRIMARY KEY(`idlogdata_anonymization`), KEY(`job_start_date`)");
    }

    public function uninstall()
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    public function getAllEntries()
    {
        $entries = Db::fetchAll(sprintf('SELECT * FROM %s', $this->tablePrefixed));

        return $this->enrichEntries($entries);
    }

    public function getEntry($idLogData)
    {
        $scheduled = Db::fetchRow(sprintf('SELECT * FROM %s WHERE idlogdata_anonymization = ?', $this->tablePrefixed), array($idLogData));

        return $this->enrichEntry($scheduled);
    }

    public function getNextScheduledAnonymizationId()
    {
        $scheduled = Db::fetchOne(sprintf('SELECT idlogdata_anonymization FROM %s WHERE job_start_date is null ORDER BY idlogdata_anonymization asc LIMIT 1', $this->tablePrefixed));

        if (!empty($scheduled)) {
            return (int) $scheduled;
        }
        return $scheduled;
    }

    private function enrichEntries($entries)
    {
        if (empty($entries)) {
            return array();
        }

        foreach ($entries as $index => $entry) {
            $entries[$index] = $this->enrichEntry($entry);
        }

        return $entries;
    }

    private function enrichEntry($entry)
    {
        if (empty($entry)) {
            return $entry;
        }

        $entry['sites'] = array();
        if (!empty($entry['idsites'])) {
            $entry['idsites'] = json_decode($entry['idsites'], true);
            foreach ($entry['idsites'] as $idSite) {
                try {
                    $entry['sites'][] = Site::getNameFor($idSite);
                } catch (\Exception$e) {
                    // site might be deleted
                    $entry['sites'][] = 'Site ID: '. $idSite;
                }
            }
        } else {
            $entry['idsites'] = null;
            $entry['sites'][] = 'All Websites';
        }

        if (!empty($entry['unset_visit_columns'])) {
            $entry['unset_visit_columns'] = json_decode($entry['unset_visit_columns'], true);
        } else {
            $entry['unset_visit_columns'] = array();
        }

        if (!empty($entry['unset_link_visit_action_columns'])) {
            $entry['unset_link_visit_action_columns'] = json_decode($entry['unset_link_visit_action_columns'], true);
        } else {
            $entry['unset_link_visit_action_columns'] = array();
        }

        $entry['anonymize_ip'] = !empty($entry['anonymize_ip']);
        $entry['anonymize_location'] = !empty($entry['anonymize_location']);
        $entry['anonymize_userid'] = !empty($entry['anonymize_userid']);
        $entry['idlogdata_anonymization'] = (int) $entry['idlogdata_anonymization'];

        return $entry;
    }

    public function scheduleEntry($requester, $idSites, $dateString, $anonymizeIp, $anonymizeLocation, $anonymizeUserId, $unsetVisitColumns, $unsetLinkVisitActionColumns, $willBeStartedNow = false)
    {
        BaseValidator::check('date', $dateString, [new NotEmpty()]);

        list($startDate, $endDate) = $this->getStartAndEndDate($dateString); // make sure valid date

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
        if (!empty($idSites) && $idSites !== 'all') {
            $idSites = array_map('intval', $idSites);
            $idSites = json_encode($idSites);
        } else {
            $idSites = null;
        }

        if (!$anonymizeIp && !$anonymizeLocation && !$anonymizeUserId && empty($unsetVisitColumns) && empty($unsetLinkVisitActionColumns)) {
            throw new \Exception('Nothing is selected to be anonymized');
        }

        $db = Db::get();
        $now = Date::now()->getDatetime();

        $values = array(
            'idsites' => $idSites,
            'date_start' => $startDate,
            'date_end' => $endDate,
            'anonymize_ip' => !empty($anonymizeIp) ? 1 : 0,
            'anonymize_location' => !empty($anonymizeLocation) ? 1 : 0,
            'anonymize_userid' => !empty($anonymizeUserId) ? 1 : 0,
            'unset_visit_columns' => json_encode($unsetVisitColumns),
            'unset_link_visit_action_columns' => json_encode($unsetLinkVisitActionColumns),
            'scheduled_date' => Date::now()->getDatetime(),
            'job_start_date' => $willBeStartedNow ? $now : null, // we set a start_date when executing from CLI to avoid a race condition to prevent a task from operating a scheduled entry
            'requester' => $requester,
        );
        $columns = implode('`,`', array_keys($values));
        $fields = Common::getSqlStringFieldsArray($values);

        $sql = sprintf('INSERT INTO %s (`%s`) VALUES(%s)', $this->tablePrefixed, $columns, $fields);
        $bind = array_values($values);

        $db->query($sql, $bind);

        $id = $db->lastInsertId();

        return (int) $id;
    }

    private function updateEntry($idLogDataAnonymization, $field, $value)
    {
        $query = sprintf('UPDATE %s SET %s = ? WHERE idlogdata_anonymization = ?', $this->tablePrefixed, $field);

        Db::query($query, array($value, $idLogDataAnonymization));
    }

    public function setCallbackOnOutput($callback)
    {
        $this->onOutputCallback = $callback;
    }

    private function appendToOutput($index, &$schedule, $message)
    {
        $schedule['output'] .= $message . "\n";
        $this->updateEntry($index, 'output', $schedule['output']);

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

    public function executeScheduledEntry($idLogData)
    {
        $schedule = $this->getEntry($idLogData);

        if (empty($schedule)) {
            throw new \Exception('Entry not found');
        }

        $this->updateEntry($idLogData, 'job_start_date', Date::now()->getDatetime());

        $idSites = $schedule['idsites'];
        $startDate = $schedule['date_start'];
        $endDate = $schedule['date_end'];

        if (empty($idSites)) {
            $idSites = null;
            $this->appendToOutput($idLogData, $schedule, "Running behaviour on all sites.");
        } else {
            $this->appendToOutput($idLogData, $schedule, 'Running behaviour on these sites: ' . implode(', ', $idSites));
        }

        $this->appendToOutput($idLogData, $schedule, sprintf("Applying this to visits between '%s' and '%s'.", $startDate, $endDate));

        if ($schedule['anonymize_ip'] || $schedule['anonymize_location'] || $schedule['anonymize_userid']) {
            $this->appendToOutput($idLogData, $schedule, 'Starting to anonymize visit information.');
            try {
                $numAnonymized = $this->logDataAnonymizer->anonymizeVisitInformation($idSites, $startDate, $endDate, $schedule['anonymize_ip'], $schedule['anonymize_location'], $schedule['anonymize_userid']);
                $this->appendToOutput($idLogData, $schedule, 'Number of anonymized IP and/or location and/or User ID: ' . $numAnonymized);
            } catch (\Exception $e) {
                $this->appendToOutput($idLogData, $schedule, 'Failed to anonymize IP and/or location and/or User ID:' . $e->getMessage());
            }
        }

        if (!empty($schedule['unset_visit_columns'])) {
            try {
                $this->appendToOutput($idLogData, $schedule, 'Starting to unset log_visit table entries.');
                $numColumnsUnset = $this->logDataAnonymizer->unsetLogVisitTableColumns($idSites, $startDate, $endDate, $schedule['unset_visit_columns']);
                $this->appendToOutput($idLogData, $schedule, 'Number of unset log_visit table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($idLogData, $schedule, 'Failed to unset log_visit table entries:' . $e->getMessage());
            }

            try {
                $this->appendToOutput($idLogData, $schedule, 'Starting to unset log_conversion table entries (if possible).');
                $numColumnsUnset = $this->logDataAnonymizer->unsetLogConversionTableColumns($idSites, $startDate, $endDate, $schedule['unset_visit_columns']);
                $this->appendToOutput($idLogData, $schedule, 'Number of unset log_conversion table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($idLogData, $schedule, 'Failed to unset log_conversion table entries:' . $e->getMessage());
            }
        }

        if (!empty($schedule['unset_link_visit_action_columns'])) {
            try {
                $this->appendToOutput($idLogData, $schedule, 'Starting to unset log_link_visit_action table entries.');
                $numColumnsUnset = $this->logDataAnonymizer->unsetLogLinkVisitActionColumns($idSites, $startDate, $endDate, $schedule['unset_link_visit_action_columns']);
                $this->appendToOutput($idLogData, $schedule, 'Number of unset log_link_visit_action table entries: ' . $numColumnsUnset);
            } catch (\Exception $e) {
                $this->appendToOutput($idLogData, $schedule, 'Failed to unset log_link_visit_action table entries:' . $e->getMessage());
            }
        }

        $this->updateEntry($idLogData, 'job_finish_date', Date::now()->getDatetime());
    }

}
