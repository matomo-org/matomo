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
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Moves scheduled reports off the retired `idGoal=0` report unique ids. The ecommerce order report is kept
 * only where the site has ecommerce enabled, read from the site setting rather than the plugin state.
 */
class Updates_6_0_0_b9 extends Updates
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

        // the migrations of all pending updates are listed before any runs, so an older schema may not have these yet
        if (!DbHelper::tableExists($reportTable) || !array_key_exists('ecommerce', DbHelper::getTableColumns($siteTable))) {
            return [];
        }

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
     * Collapses only duplicates the replacement produces.
     *
     * @param array<string, string> $replacements
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

            // a replaced id absorbs at most one later entry naming the same report
            if (isset($emitted[$reportUniqueId])) {
                if ($isReplaced) {
                    continue;
                }

                if (isset($fromReplaced[$reportUniqueId])) {
                    unset($fromReplaced[$reportUniqueId]);
                    continue;
                }
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
