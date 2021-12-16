<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.6.0-b2.
 */
class Updates_4_6_0_b4 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @param Updater $updater
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];
        $sites = new Model();
        $idSites = $sites->getSitesId();

        $doneFlagsToMigrate = [];
        foreach ($idSites as $idSite) {
            $segmentStrings = Rules::getSegmentsToProcess([$idSite]);

            foreach ($segmentStrings as $segmentString) {
                try {
                    $segment = new Segment($segmentString, [$idSite]);
                } catch (\Exception $e) {
                    continue;
                }
                if ($segment->getOriginalString() === $segment->getString()) {
                    continue;
                }

                $segmentsToAppend = [VisitFrequencyAPI::NEW_VISITOR_SEGMENT, VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT];
                foreach ($segmentsToAppend as $segmentToAppend) {
                    // we need to migrate the existing archive
                    $oldSegmentString = Segment::combine($segment->getString(), SegmentExpression::AND_DELIMITER, $segmentToAppend);
                    $newSegmentString = Segment::combine($segment->getOriginalString(), SegmentExpression::AND_DELIMITER, $segmentToAppend);
                    $oldSegmentHash = Segment::getSegmentHash($oldSegmentString);
                    $newSegmentHash = Segment::getSegmentHash($newSegmentString);

                    if ($oldSegmentHash === $newSegmentHash) {
                        continue;
                    }

                    $doneFlagsToMigrate['done' . $oldSegmentHash . '.Goals'] = 'done' . $newSegmentHash . '.Goals';
                    $doneFlagsToMigrate['done' . $oldSegmentHash . '.VisitsSummary'] = 'done' . $newSegmentHash . '.VisitsSummary';
                    $doneFlagsToMigrate['done' . $oldSegmentHash . '.UserCountry'] =  'done' . $newSegmentHash . '.UserCountry';
                }
            }
        }

        if (!empty($doneFlagsToMigrate)) {
            foreach (ArchiveTableCreator::getTablesArchivesInstalled() as $table) {
                if (strpos($table, 'numeric') === false) {
                    continue;
                }

                $sqlPlaceholders = Common::getSqlStringFieldsArray($doneFlagsToMigrate);
                $bind = array_keys($doneFlagsToMigrate);

                $selectSql = sprintf('SELECT 1 FROM %s where `name` in (%s) LIMIT 1', $table, $sqlPlaceholders);
                $archiveTableHasDoneFlags = Db::fetchOne($selectSql, $bind);
                if (!$archiveTableHasDoneFlags) {
                    continue;
                }

                $sql = 'update ' . $table . ' set `name` = (case';
                foreach ($doneFlagsToMigrate as $oldDoneFlag => $newDoneFlag) {
                    $sql .= " when `name`   = '$oldDoneFlag' then '$newDoneFlag' ";
                }
                $sql .= ' else `name` end) where `name` in (' . $sqlPlaceholders . ')';

                Db::query($sql, $bind);
                $migrations[] = $this->migration->db->boundSql($sql, $bind, [Migration\Db\Sql::ERROR_CODE_DUPLICATE_ENTRY]);
            }
        }

        return $migrations;
    }

    /**
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
