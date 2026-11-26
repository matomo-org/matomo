<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * @group TableLogActionTest
 * @group Tracker
 */
class TableLogActionTest extends IntegrationTestCase
{
    public function testLoadIdsActionShouldTruncateNamesLongerThanTheColumn()
    {
        $longName = str_repeat('ä', 5000);

        $ids = TableLogAction::loadIdsAction(['idaction_event_category' => [$longName, Action::TYPE_EVENT_CATEGORY]]);

        $this->assertNotEmpty($ids['idaction_event_category']);
        $this->assertSame(str_repeat('ä', 4096), $this->getActionName($ids['idaction_event_category']));
    }

    public function testLoadIdsActionShouldReuseTheActionOfATruncatedName()
    {
        $prefix = str_repeat('a', 4096);

        $first = TableLogAction::loadIdsAction(['name' => [$prefix . 'first', Action::TYPE_EVENT_CATEGORY]]);
        $second = TableLogAction::loadIdsAction(['name' => [$prefix . 'second', Action::TYPE_EVENT_CATEGORY]]);
        $exact = TableLogAction::loadIdsAction(['name' => [$prefix, Action::TYPE_EVENT_CATEGORY]]);

        $this->assertEquals($first['name'], $second['name']);
        $this->assertEquals($first['name'], $exact['name']);
    }

    public function testLoadIdsActionShouldNotChangeNamesWithinTheColumnLength()
    {
        $name = str_repeat('ä', 4096);

        $ids = TableLogAction::loadIdsAction(['name' => [$name, Action::TYPE_EVENT_CATEGORY]]);

        $this->assertSame($name, $this->getActionName($ids['name']));
    }

    private function getActionName($idAction): string
    {
        return Db::fetchOne('SELECT name FROM ' . Common::prefixTable('log_action') . ' WHERE idaction = ?', [$idAction]);
    }
}
