<?php

namespace Piwik\Tests\Framework\Mock\Plugin;

use Piwik\Plugins\CoreHome\Tracker\LogTable\Action;
use Piwik\Plugins\CoreHome\Tracker\LogTable\Conversion;
use Piwik\Plugins\CoreHome\Tracker\LogTable\ConversionItem;
use Piwik\Plugins\CoreHome\Tracker\LogTable\LinkVisitAction;
use Piwik\Plugins\CoreHome\Tracker\LogTable\Visit;

class LogTablesProvider extends \Piwik\Plugin\LogTablesProvider
{
    public function __construct()
    {
    }

    public function getAllLogTables()
    {
        return array(
            new Visit(),
            new Action(),
            new LinkVisitAction(),
            new ConversionItem(),
            new Conversion(),
            new CustomUserLogTable(),
            new OtherCustomUserLogTable()
        );
    }
}
