<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;

class Updates_6_0_0_b7 extends Updates
{
    /**
     * A Continent segment with `!=` matched the visits of the very continent it excludes. Its saved
     * segments keep their definition, so the reports archived for them are rearchived, as far back
     * as `rearchive_reports_in_past_last_n_months` allows.
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $table = Common::prefixTable('segment');
        if (!DbHelper::tableExists($table)) {
            return [];
        }

        $startDate = Piwik::getEarliestDateToRearchive();
        if (empty($startDate)) {
            return [];
        }

        $segments = Db::fetchAll(
            sprintf('SELECT idsegment, definition, enable_only_idsite FROM `%s` WHERE deleted = 0 AND definition LIKE ?', $table),
            ['%continentCode%']
        );

        $migrations = [];
        foreach ($segments as $segment) {
            // a definition may have been stored with its operators encoded
            if (stripos(urldecode($segment['definition']), 'continentCode!=') === false) {
                continue;
            }

            $idSegment = (int) $segment['idsegment'];
            $definition = $segment['definition'];
            $idSites = empty($segment['enable_only_idsite']) ? 'all' : [(int) $segment['enable_only_idsite']];
            $migrationHint = sprintf(
                './console core:invalidate-report-data --sites=%s --dates=%s,today --segment=%d',
                is_array($idSites) ? implode(',', $idSites) : $idSites,
                $startDate->toString(),
                $idSegment
            );

            $migrations[] = new CustomMigration(function () use ($idSegment, $definition, $idSites) {
                try {
                    // only the definition is kept for the rearchiving, the sites are resolved then
                    $segment = new Segment($definition, is_array($idSites) ? $idSites : []);
                } catch (\Exception $e) {
                    // a segment no longer valid, eg. of a plugin since removed, has nothing to rearchive
                    StaticContainer::get(LoggerInterface::class)->warning(
                        'Segment {idSegment} is no longer valid, its reports are not rearchived: {ex}',
                        ['idSegment' => $idSegment, 'ex' => $e]
                    );
                    return;
                }

                $invalidator = StaticContainer::get(ArchiveInvalidator::class);
                $invalidator->scheduleReArchiving($idSites, null, null, null, $segment);
            }, $migrationHint);
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
