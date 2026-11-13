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
use Piwik\CronArchive\ReArchiveList;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\Date;
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
    private const CONTINENT_CODES = ['eur', 'asi', 'amc', 'amn', 'ams', 'afr', 'ant', 'oce'];

    /**
     * A Continent segment now applies its operator to the continent of the visit country, where it used to
     * match the countries of the continent it names whatever the operator, so `!=` matched the very continent
     * it excludes. Its saved segments keep their definition, so the reports archived for them since the start
     * of the current month are rearchived, or only invalidated for the segments `core:archive` does not archive.
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $table = Common::prefixTable('segment');
        if (!DbHelper::tableExists($table)) {
            return [];
        }

        if (empty(Piwik::getEarliestDateToRearchive())) {
            return [];
        }

        $startOfMonth = Date::factory(Date::now()->toString('Y-m') . '-01');

        $segments = Db::fetchAll(
            sprintf('SELECT idsegment, definition, enable_only_idsite, auto_archive, ts_created, ts_last_edit FROM `%s` WHERE deleted = 0 AND definition LIKE ? ORDER BY idsegment', $table),
            ['%continentCode%']
        );

        /** @var SegmentArchiving $segmentArchiving */
        $segmentArchiving = StaticContainer::get(SegmentArchiving::class);

        $allIdSites = $this->getAllIdSites();
        $segmentsToRearchive = [];
        foreach ($segments as $segment) {
            $idSegment = (int) $segment['idsegment'];
            $definition = $segment['definition'];

            // like Segment, a definition may have been stored encoded, separators included
            $parsedDefinitions = array_filter([$this->parseConditions($definition), $this->parseConditions(urldecode($definition))], 'is_array');
            if (empty($parsedDefinitions)) {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Segment {idSegment} could not be parsed, its reports are not rearchived',
                    ['idSegment' => $idSegment]
                );
                continue;
            }

            if (!in_array(true, array_map([$this, 'hasAChangedContinentCondition'], $parsedDefinitions), true)) {
                continue;
            }

            $idSites = empty($segment['enable_only_idsite']) ? 'all' : [(int) $segment['enable_only_idsite']];
            // a segment of a site deleted before segments were deleted with their site
            if (is_array($idSites) && !in_array($idSites[0], $allIdSites, true)) {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Segment {idSegment} is on a site that no longer exists, its reports are not rearchived',
                    ['idSegment' => $idSegment]
                );
                continue;
            }

            $startDate = $this->getStartDate($segmentArchiving, $segment, $startOfMonth);
            $isAutoArchived = $segmentArchiving->isAutoArchivingEnabledFor($segment);

            // the same definition saved by several users, encoded or not, shares its archives, so it is rearchived once,
            // from the earliest start date of its copies
            $key = urldecode($definition) . '|' . json_encode($idSites);
            if (isset($segmentsToRearchive[$key])) {
                $segmentToRearchive = &$segmentsToRearchive[$key];
                $segmentToRearchive['startDate'] = $startDate->isEarlier($segmentToRearchive['startDate']) ? $startDate : $segmentToRearchive['startDate'];
                $segmentToRearchive['isAutoArchived'] = $segmentToRearchive['isAutoArchived'] || $isAutoArchived;
                unset($segmentToRearchive);
                continue;
            }

            $segmentsToRearchive[$key] = [
                'idSegment' => $idSegment,
                'definition' => $definition,
                'idSites' => $idSites,
                'startDate' => $startDate,
                'isAutoArchived' => $isAutoArchived,
            ];
        }

        $migrations = [];
        foreach ($segmentsToRearchive as $segmentToRearchive) {
            ['idSegment' => $idSegment, 'definition' => $definition, 'idSites' => $idSites, 'startDate' => $startDate] = $segmentToRearchive;

            // reports are rearchived up to yesterday, as today's are archived again anyway
            if (!$startDate->isEarlier(Date::today())) {
                continue;
            }

            if (!$segmentToRearchive['isAutoArchived']) {
                // core:archive never archives this segment and would only drop its invalidations,
                // so its archives are marked as invalidated, to be archived again when viewed
                $migrations[] = new CustomMigration(function () use ($idSegment, $definition, $idSites, $startDate) {
                    $this->invalidateArchives($idSegment, $definition, $idSites, $startDate);
                }, '');
                continue;
            }

            $migrationHint = sprintf(
                './console core:invalidate-report-data --sites=%s --dates=%s,today --segment=%d',
                is_array($idSites) ? implode(',', $idSites) : $idSites,
                $startDate->toString(),
                $idSegment
            );

            $migrations[] = new CustomMigration(function () use ($idSegment, $definition, $idSites, $startDate) {
                $this->scheduleReArchiving($idSegment, $definition, $idSites, $startDate);
            }, $migrationHint);
        }

        return $migrations;
    }

    /**
     * The segment is rearchived from where it would be when its definition is edited, see
     * process_new_segments_from, but not before the start of the current month.
     */
    private function getStartDate(SegmentArchiving $segmentArchiving, array $segment, Date $startOfMonth): Date
    {
        $startDate = $segmentArchiving->getReArchiveSegmentStartDate($segment);
        if (empty($startDate) || $startDate->isEarlier($startOfMonth)) {
            return $startOfMonth;
        }

        return Date::factory($startDate->toString());
    }

    /**
     * @param 'all'|int[] $idSites
     */
    private function scheduleReArchiving(int $idSegment, string $definition, $idSites, Date $startDate): void
    {
        $segmentsToRearchive = $this->getSegmentsToRearchive($idSegment, $definition, $idSites);
        if (empty($segmentsToRearchive)) {
            return;
        }

        // the entries ArchiveInvalidator::scheduleReArchiving() adds, added at once as the list is rewritten on each addition
        $items = [];
        foreach ($segmentsToRearchive as [$sites, $segment]) {
            $items[] = json_encode([
                'idSites' => $sites,
                'pluginName' => null,
                'report' => null,
                'startDate' => $startDate->getTimestamp(),
                'segment' => $segment->getOriginalString(),
            ]);
        }

        try {
            (new ReArchiveList())->add($items);
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'The reports of segment {idSegment} could not be scheduled for rearchiving: {exception}',
                ['idSegment' => $idSegment, 'exception' => $e->getMessage()]
            );
        }
    }

    /**
     * @param 'all'|int[] $idSites
     */
    private function invalidateArchives(int $idSegment, string $definition, $idSites, Date $startDate): void
    {
        $segmentsToRearchive = $this->getSegmentsToRearchive($idSegment, $definition, $idSites);
        if (empty($segmentsToRearchive)) {
            return;
        }

        $dates = [];
        for ($date = $startDate; $date->isEarlier(Date::today()); $date = $date->addDay(1)) {
            $dates[] = $date;
        }

        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        foreach ($segmentsToRearchive as [$sites, $segment]) {
            try {
                $invalidator->markArchivesAsInvalidated(
                    is_array($sites) ? $sites : $this->getAllIdSites(),
                    $dates,
                    'day',
                    $segment,
                    $cascadeDown = false,
                    $forceInvalidateNonexistentRanges = false,
                    $name = null,
                    $ignorePurgeLogDataDate = false,
                    $doNotCreateInvalidations = true
                );
            } catch (\Throwable $e) {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'The reports of segment {idSegment} could not be invalidated: {exception}',
                    ['idSegment' => $idSegment, 'exception' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * A segment of every site may only be valid in the context of a site, eg. one on a custom
     * dimension, so it is then rearchived site by site.
     *
     * @param 'all'|int[] $idSites
     * @return array<array{0: 'all'|int[], 1: Segment}>
     */
    private function getSegmentsToRearchive(int $idSegment, string $definition, $idSites): array
    {
        $segment = $this->createSegment($definition, is_array($idSites) ? $idSites : []);
        if ($segment) {
            return [[$idSites, $segment]];
        }

        $segmentsToRearchive = [];
        if (!is_array($idSites)) {
            foreach ($this->getAllIdSites() as $idSite) {
                $segment = $this->createSegment($definition, [$idSite]);
                if ($segment) {
                    $segmentsToRearchive[] = [[$idSite], $segment];
                }
            }
        }

        if (empty($segmentsToRearchive)) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'Segment {idSegment} is no longer valid on any of its sites, its reports are not rearchived',
                ['idSegment' => $idSegment]
            );
        }

        return $segmentsToRearchive;
    }

    /**
     * @return int[]
     */
    private function getAllIdSites(): array
    {
        return array_map('intval', array_column(Db::fetchAll(sprintf('SELECT idsite FROM `%s`', Common::prefixTable('site'))), 'idsite'));
    }

    /**
     * @return array|null the conditions as returned by {@see SegmentExpression::parseSubExpressions()}, null if the definition cannot be parsed
     */
    private function parseConditions(string $definition): ?array
    {
        try {
            return (new SegmentExpression($definition))->parseSubExpressions();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Only `==` on a continent matches the same visits as before, any other Continent condition changed,
     * except an empty value, as the tracker always stores a country, `xx` when it is unknown.
     * The legacy Tibet code, now counted in Asia, is left aside: current geolocation no longer sets it.
     */
    private function hasAChangedContinentCondition(array $conditions): bool
    {
        foreach ($conditions as $orConditions) {
            foreach ($orConditions as $condition) {
                if ($condition[SegmentExpression::INDEX_OPERAND_NAME] !== 'continentCode') {
                    continue;
                }

                $operator = $condition[SegmentExpression::INDEX_OPERAND_OPERATOR];
                $isSameAsBefore = in_array($operator, [SegmentExpression::MATCH_IS_NULL_OR_EMPTY, SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY], true)
                    || ($operator === SegmentExpression::MATCH_EQUAL
                        && in_array(strtolower((string) $condition[SegmentExpression::INDEX_OPERAND_VALUE]), self::CONTINENT_CODES, true));
                if (!$isSameAsBefore) {
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
