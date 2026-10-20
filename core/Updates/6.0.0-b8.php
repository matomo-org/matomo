<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Moves scheduled reports off the goal overview reports carrying `idGoal=0`, which reported ecommerce
 * order figures (`Goals.get`) or all-goals figures (the conversion distributions) under a goals
 * overview name. An ecommerce order report is kept only where the site has ecommerce enabled, as that
 * report is not available on the other sites. The Ecommerce plugin being inactive during the update
 * does not change the move, so the report is back once the plugin is activated again.
 */
class Updates_6_0_0_b8 extends Updates
{
    private const ECOMMERCE_ORDER_REPLACEMENTS = [
        'Goals_get_idGoal--0' => 'Goals_get_idGoal--ecommerceOrder',
    ];

    private const ALL_GOALS_REPLACEMENTS = [
        'Goals_get_idGoal--0'                      => 'Goals_get',
        'Goals_getVisitsUntilConversion_idGoal--0' => 'Goals_getVisitsUntilConversion',
        'Goals_getDaysToConversion_idGoal--0'      => 'Goals_getDaysToConversion',
    ];

    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $reportTable = Common::prefixTable('report');
        $siteTable   = Common::prefixTable('site');

        $storedReports = Db::fetchAll(
            "SELECT r.idreport, r.reports, s.ecommerce FROM `$reportTable` r
               LEFT JOIN `$siteTable` s ON s.idsite = r.idsite
              WHERE r.reports LIKE ?",
            ['%idGoal--0%']
        );

        $migrations = [];

        foreach ($storedReports as $storedReport) {
            $reportUniqueIds = json_decode($storedReport['reports'], true);

            if (!is_array($reportUniqueIds)) {
                continue;
            }

            $replacements = self::ALL_GOALS_REPLACEMENTS;

            if (1 === (int) $storedReport['ecommerce']) {
                $replacements = self::ECOMMERCE_ORDER_REPLACEMENTS + $replacements;
            }

            $movedReportUniqueIds = $this->moveReportUniqueIds($reportUniqueIds, $replacements);

            if ($movedReportUniqueIds === $reportUniqueIds) {
                continue;
            }

            $migrations[] = $this->migration->db->boundSql(
                "UPDATE `$reportTable` SET reports = ? WHERE idreport = ? AND reports = ?",
                [json_encode($movedReportUniqueIds), $storedReport['idreport'], $storedReport['reports']]
            );
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    /**
     * Keeps the first occurrence of a report the replacement itself ends up naming twice, so a
     * selection that deliberately named the same report twice is kept as it was.
     *
     * @param array<mixed> $reportUniqueIds
     * @param array<string, string> $replacements
     * @return array<mixed>
     */
    private function moveReportUniqueIds(array $reportUniqueIds, array $replacements): array
    {
        $moved        = [];
        $emitted      = [];
        $fromReplaced = [];

        foreach ($reportUniqueIds as $reportUniqueId) {
            if (!is_string($reportUniqueId)) {
                $moved[] = $reportUniqueId;
                continue;
            }

            $isReplaced     = isset($replacements[$reportUniqueId]);
            $reportUniqueId = $replacements[$reportUniqueId] ?? $reportUniqueId;

            if (isset($emitted[$reportUniqueId]) && ($isReplaced || isset($fromReplaced[$reportUniqueId]))) {
                continue;
            }

            if ($isReplaced) {
                $fromReplaced[$reportUniqueId] = true;
            }

            $moved[]                  = $reportUniqueId;
            $emitted[$reportUniqueId] = true;
        }

        return $moved;
    }
}
