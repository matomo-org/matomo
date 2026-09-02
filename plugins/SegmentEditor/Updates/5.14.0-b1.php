<?php

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_5_14_0_b1 extends Updates
{
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $starTable = "`idsegment` INT(11) NOT NULL,
                       `login` VARCHAR(100) NOT NULL,
                       PRIMARY KEY (`idsegment`, `login`)";

        return [
            $this->migration->db->createTable('user_segment_star', $starTable),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
