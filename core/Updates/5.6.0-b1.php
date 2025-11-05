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
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\Referrers\Archiver;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;

/**
 * Update for version 5.6.0-b1
 */
class Updates_5_6_0_b1 extends PiwikUpdates
{
    /**
     * Migrations
     *
     * Reports for AI referrers have changed with this release. The previous records named Referrers_urlByAIAssistant will no longer be used.
     * Instead, two new reports (with different secondary dimensions) have been introduced.
     * To invalidate the reports for dates after the update to 5.5.0-b1 (the version, which contained the new AI referrer reports), we "misuse"
     * the `changes` table. As a change record was added with that feature, we can assume that the date, where that change record is added to
     * the database, is the date where updating to 5.5.0 or later happened. So we use this date for invalidations.
     *
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        if (!DbHelper::tableExists(Common::prefixTable('changes'))) {
            return [];
        }

        $invalidationStartDate = Db::fetchOne('SELECT DATE(created_time) FROM ' . Common::prefixTable('changes') . ' WHERE plugin_name = ? AND version = ? LIMIT 1', [
            'Referrers', '5.5.0-b1',
        ]);

        $migrations = [];

        if ($invalidationStartDate) {
            $commandToExecute = sprintf(
                './console core:invalidate-report-data --dates=%s,today --plugin=Referrers.' . Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME,
                $invalidationStartDate
            );

            $migrations[] = new CustomMigration(function () use ($invalidationStartDate) {
                $invalidator = StaticContainer::get(ArchiveInvalidator::class);
                $invalidator->scheduleReArchiving('all', 'Referrers', Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME, Date::factory($invalidationStartDate));
            }, $commandToExecute);

            // Note: There is no need to also invalidate `Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME`, as it's automatically built within the same record builder
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
