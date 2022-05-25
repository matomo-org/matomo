<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager\Dao;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Matomo\Network\IP;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\PrivacyManager\Tracker\RequestProcessor;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Tracker\Model;
use Exception;

class LogDataAnonymizer
{
    const NUM_ROWS_UPDATE_AT_ONCE = 10000;
    protected $COLUMNS_BLACKLISTED = array('idvisit', 'idvisitor', 'idsite', 'visit_last_action_time', 'config_id', 'location_ip', 'idlink_va', 'server_time', 'idgoal', 'buster', 'idorder');

    /**
     * @var string
     */
    private $logVisitTable;

    public function __construct()
    {
        $this->logVisitTable = Common::prefixTable('log_visit');
    }

    public function anonymizeVisitInformation($idSites, $startDate, $endDate, $anonymizeIp, $anonimizeLocation, $anonymizeUserId)
    {
        if (!$anonymizeIp && !$anonimizeLocation && !$anonymizeUserId) {
            return 0; // nothing to do
        }

        if (empty($idSites)) {
            $idSites = $this->getAllIdSitesString($this->logVisitTable);
        } else {
            $idSites = array_map('intval', $idSites);
        }

        if (empty($idSites)) {
            return 0; // no visit tracked yet, the idsite in() would otherwise fail
        }

        $idSites = implode(', ', $idSites);

        $numVisitsToUpdate = $this->getNumVisitsInTimeRange($idSites, $startDate, $endDate);

        if (empty($numVisitsToUpdate)) {
            return 0;
        }

        $privacyConfig = new Config();
        $minimumIpAddressMaskLength = 2;
        $ipMask = max($minimumIpAddressMaskLength, $privacyConfig->ipAddressMaskLength);

        $numRecordsUpdated = 0;
        $trackerModel = new Model();
        $geolocator = new VisitorGeolocator();

        for ($i = 0; $i < $numVisitsToUpdate; $i = $i + self::NUM_ROWS_UPDATE_AT_ONCE) {
            $offset = $i;
            $limit = self::NUM_ROWS_UPDATE_AT_ONCE;
            if (($offset + $limit) > $numVisitsToUpdate) {
                $limit = $numVisitsToUpdate % $limit;
            }

            $sql = sprintf('SELECT idsite, idvisit, location_ip, user_id, location_longitude, location_latitude, location_city, location_region, location_country FROM %s WHERE idsite in (%s) and visit_last_action_time >= ? and visit_last_action_time <= ? ORDER BY idsite, visit_last_action_time, idvisit LIMIT %d OFFSET %d', $this->logVisitTable, $idSites, $limit, $offset);
            $rows = Db::query($sql, array($startDate, $endDate))->fetchAll();

            foreach ($rows as $row) {
                $ipObject = IP::fromBinaryIP($row['location_ip']);
                $ipString = $ipObject->toString();
                $ipAnonymized = IPAnonymizer::applyIPMask($ipObject, $ipMask);
                $update = array();

                if ($anonymizeIp) {
                    if ($ipString !== $ipAnonymized->toString()) {
                        // needs updating
                        $update['location_ip'] = $ipAnonymized->toBinary();
                    }
                }

                if ($anonymizeUserId && isset($row['user_id']) && $row['user_id'] !== false && $row['user_id'] !== '') {
                    $update['user_id'] = RequestProcessor::anonymizeUserId($row['user_id']);
                }

                if ($anonimizeLocation) {
                    $location = $geolocator->getLocation(array('ip' => $ipAnonymized->toString()));

                    $keys = array(
                        'location_longitude' => LocationProvider::LONGITUDE_KEY,
                        'location_latitude' => LocationProvider::LATITUDE_KEY,
                        'location_city' => LocationProvider::CITY_NAME_KEY,
                        'location_region' => LocationProvider::REGION_CODE_KEY,
                        'location_country' => LocationProvider::COUNTRY_CODE_KEY,
                    );

                    foreach ($keys as $name => $val) {
                        $newLocationData = null;
                        if (isset($location[$val]) && $location[$val] !== false) {
                            $newLocationData = $location[$val];
                        }
                        if ($newLocationData !== $row[$name]) {
                            $update[$name] = $newLocationData;
                        }
                    }
                }
                if (!empty($update)) {
                    $trackerModel->updateVisit($row['idsite'], $row['idvisit'], $update);
                    $numRecordsUpdated++;
                }
            }
            unset($rows);
        }

        return $numRecordsUpdated;
    }

    public function unsetLogVisitTableColumns($idSites, $startDate, $endDate, $columns)
    {
        return $this->unsetLogTableColumns('log_visit', 'visit_last_action_time', $idSites, $startDate, $endDate, $columns);
    }

    public function unsetLogConversionTableColumns($idSites, $startDate, $endDate, $visitColumns)
    {
        $columnsToUnset = array();

        $table = 'log_conversion';
        $logTableFields = $this->getAvailableColumnsWithDefaultValue(Common::prefixTable($table));
        foreach ($visitColumns as $column) {
            // we do not fail if a specified column does not exist here as this is applied to visit columns
            // and some visit columns may not exist in log_conversion. We do not want to fail in this case
            if (array_key_exists($column, $logTableFields)) {
                $columnsToUnset[] = $column;
            }
        }

        return $this->unsetLogTableColumns($table, 'server_time', $idSites, $startDate, $endDate, $columnsToUnset);
    }

    public function unsetLogLinkVisitActionColumns($idSites, $startDate, $endDate, $columns)
    {
        return $this->unsetLogTableColumns('log_link_visit_action', 'server_time', $idSites, $startDate, $endDate, $columns);
    }

    public function checkAllVisitColumns($visitColumns)
    {
        $this->areAllColumnsValid('log_visit', $visitColumns);
        return null;
    }

    public function checkAllLinkVisitActionColumns($linkVisitActionColumns)
    {
        $this->areAllColumnsValid('log_link_visit_action', $linkVisitActionColumns);
        return null;
    }

    public function getAvailableVisitColumnsToAnonymize()
    {
        return $this->getAvailableColumnsWithDefaultValue(Common::prefixTable('log_visit'));
    }

    public function getAvailableLinkVisitActionColumnsToAnonymize()
    {
        return $this->getAvailableColumnsWithDefaultValue(Common::prefixTable('log_link_visit_action'));
    }

    private function areAllColumnsValid($table, $columns)
    {
        if (empty($columns)) {
            return;
        }

        $table = Common::prefixTable($table);
        $logTableFields = $this->getAvailableColumnsWithDefaultValue($table);

        foreach ($columns as $column) {
            if (!array_key_exists($column, $logTableFields)) {
                throw new Exception(sprintf('The column "%s" seems to not exist in %s or cannot be unset. Use one of %s', $column, $table, implode(', ', array_keys($logTableFields))));
            }
        }
    }

    private function unsetLogTableColumns($table, $dateColumn, $idSites, $startDate, $endDate, $columns)
    {
        if (empty($columns)) {
            return 0;
        }

        $table = Common::prefixTable($table);

        if (empty($idSites)) {
            $idSites = $this->getAllIdSitesString($table);
        } else {
            $idSites = array_map('intval', $idSites);
        }

        if (empty($idSites)) {
            return 0; // no visit tracked yet, the idsite in() would otherwise fail
        }

        $idSites = implode(', ', $idSites);

        $logTableFields = $this->getAvailableColumnsWithDefaultValue($table);

        $col = [];
        $bind = [];
        foreach ($columns as $column) {
            if (!array_key_exists($column, $logTableFields)) {
                throw new Exception(sprintf('The column "%s" cannot be unset because it has no default value or it does not exist in "%s". Use one of %s', $column, $table, implode(', ', array_keys($logTableFields))));
            }
            $col[] = $column . ' = ?';
            $bind[] = $logTableFields[$column];
        }
        $col = implode(',', $col);
        $bind[] = $startDate;
        $bind[] = $endDate;

        $sql = sprintf('UPDATE %s SET %s WHERE idsite in (%s) and %s >= ? and %s <= ?', $table, $col, $idSites, $dateColumn, $dateColumn);
        return Db::query($sql, $bind)->rowCount();
    }

    private function getNumVisitsInTimeRange($idSites, $startDate, $endDate)
    {
        $sql = sprintf('SELECT count(*) FROM %s WHERE idsite in (%s) and visit_last_action_time >= ? and visit_last_action_time <= ?', $this->logVisitTable, $idSites);
        $numVisits = Db::query($sql, array($startDate, $endDate))->fetchColumn();

        return $numVisits;
    }

    private function getAvailableColumnsWithDefaultValue($table)
    {
        $columns = DbHelper::getTableColumns($table);
        $values = array();
        foreach ($columns as $column => $config) {
            $hasDefaultKey = array_key_exists('Default', $config);

            if (in_array($column, $this->COLUMNS_BLACKLISTED, true)) {
                continue;
            } elseif (strtoupper($config['Null']) === 'NO' && $hasDefaultKey && $config['Default'] === null) {
                // we cannot unset this column as it may result in an error or random data
                continue;
            } elseif ($hasDefaultKey) {
                $values[$column] = $config['Default'];
            } elseif (strtoupper($config['Null']) === 'YES') {
                $values[$column] = null;
            }
        }
        return $values;
    }

    private function getAllIdSitesString($table)
    {
        // we need the idSites in order to use the index
        $sites = Db::query(sprintf('SELECT DISTINCT idsite FROM %s', $table))->fetchAll();
        $idSites = array();
        foreach ($sites as $site) {
            $idSites[] = (int) $site['idsite'];
        }
        return $idSites;
    }

}
