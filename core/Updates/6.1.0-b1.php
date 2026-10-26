<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;

class Updates_6_1_0_b1 extends Updates
{
    /**
     * A Continent segment with `!=` or `!@` matched the visits of the very continent it excludes. Its saved
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
            // like Segment, a definition may have been stored encoded, separators included
            if (!$this->isExcludingAContinent($segment['definition']) && !$this->isExcludingAContinent(urldecode($segment['definition']))) {
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
                $segmentsToRearchive = $this->getSegmentsToRearchive($definition, $idSites);
                if (empty($segmentsToRearchive)) {
                    StaticContainer::get(LoggerInterface::class)->warning(
                        'Segment {idSegment} is no longer valid on any of its sites, its reports are not rearchived',
                        ['idSegment' => $idSegment]
                    );
                    return;
                }

                $invalidator = StaticContainer::get(ArchiveInvalidator::class);
                foreach ($segmentsToRearchive as [$sites, $segment]) {
                    $invalidator->scheduleReArchiving($sites, null, null, null, $segment);
                }
            }, $migrationHint);
        }

        return $migrations;
    }

    /**
     * A segment of every site may only be valid in the context of a site, eg. one on a custom
     * dimension, so it is then rearchived site by site.
     *
     * @param 'all'|int[] $idSites
     * @return array<array{0: 'all'|int[], 1: Segment}>
     */
    private function getSegmentsToRearchive(string $definition, $idSites): array
    {
        $segment = $this->createSegment($definition, is_array($idSites) ? $idSites : []);
        if ($segment) {
            return [[$idSites, $segment]];
        }

        if (is_array($idSites)) {
            return [];
        }

        $segmentsToRearchive = [];
        foreach (array_column(Db::fetchAll(sprintf('SELECT idsite FROM `%s`', Common::prefixTable('site'))), 'idsite') as $idSite) {
            $segment = $this->createSegment($definition, [(int) $idSite]);
            if ($segment) {
                $segmentsToRearchive[] = [[(int) $idSite], $segment];
            }
        }

        return $segmentsToRearchive;
    }

    private function isExcludingAContinent(string $definition): bool
    {
        try {
            $conditions = (new SegmentExpression($definition))->parseSubExpressions();
        } catch (\Exception $e) {
            return false;
        }

        foreach ($conditions as $orConditions) {
            foreach ($orConditions as $condition) {
                if (
                    $condition[SegmentExpression::INDEX_OPERAND_NAME] === 'continentCode'
                    && in_array($condition[SegmentExpression::INDEX_OPERAND_OPERATOR], [SegmentExpression::MATCH_NOT_EQUAL, SegmentExpression::MATCH_DOES_NOT_CONTAIN], true)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int[] $idSites
     */
    private function createSegment(string $definition, array $idSites): ?Segment
    {
        try {
            return new Segment($definition, $idSites);
        } catch (\Exception $e) {
            return null;
        } finally {
            // the segments metadata cached for each site would otherwise add up until the update ends
            Cache::getTransientCache()->flushAll();
        }
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
