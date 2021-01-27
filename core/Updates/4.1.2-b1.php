<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API;
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

    public function doUpdate(Updater $updater)
    {
        $segmentArchiving = StaticContainer::get(CronArchive\SegmentArchiving::class);
        $timeOfLastInvalidateTime = CronArchive::getLastInvalidationTime();

        $segments = API::getInstance()->getAll();
        foreach ($segments as $segment) {
            $tsCreated = !empty($segment['ts_created']) ? Date::factory($segment['ts_created'])->getTimestamp() : 0;
            $tsLastEdit = !empty($segment['ts_last_edit']) ? Date::factory($segment['ts_last_edit'])->getTimestamp() : null;
            $timeToUse = max($tsCreated, $tsLastEdit);

            if ($timeToUse > $timeOfLastInvalidateTime) {
                $segmentArchiving->reArchiveSegment($segment);
            }
        }
    }
}