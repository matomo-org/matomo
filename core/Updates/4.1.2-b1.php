<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_4_1_2_b1 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        if (!Rules::isBrowserTriggerEnabled()) {
            $dateOfMatomo4Release = Date::factory('2020-11-23');

            $cmdStr = $this->getInvalidateCommand($dateOfMatomo4Release);

            $migrations[] = new Updater\Migration\Custom(function () use ($dateOfMatomo4Release) {
                $invalidator = StaticContainer::get(ArchiveInvalidator::class);
                $invalidator->scheduleReArchiving('all', 'VisitFrequency', null, $dateOfMatomo4Release);
            }, $cmdStr);
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    private function getInvalidateCommand(Date $dateOfMatomo4Release)
    {
        $command = "php " . PIWIK_INCLUDE_PATH . '/console core:invalidate-report-data --sites=all';
        $command .= ' --dates=' . $dateOfMatomo4Release->toString() . ',' . Date::factory('today')->toString();
        $command .= ' --plugin=VisitFrequency';
        return $command;
    }
}