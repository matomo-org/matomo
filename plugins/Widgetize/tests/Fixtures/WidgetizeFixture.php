<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize\tests\Fixtures;

use Piwik\Plugins\Goals;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our SimpleSystemTest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class WidgetizeFixture extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    private $goals = array(
        array('name' => 'Download Software',  'match' => 'url', 'pattern' => 'download',   'patternType' => 'contains', 'revenue' => 0.10),
        array('name' => 'Download Software2', 'match' => 'url', 'pattern' => 'latest.zip', 'patternType' => 'contains', 'revenue' => 0.05),
        array('name' => 'Opens Contact Form', 'match' => 'url', 'pattern' => 'contact',    'patternType' => 'contains', 'revenue' => false),
        array('name' => 'Visit Docs',         'match' => 'url', 'pattern' => 'docs',       'patternType' => 'contains', 'revenue' => false),
    );

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->setUpGoals();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    protected function setUpGoals()
    {
        $api = Goals\API::getInstance();
        foreach ($this->goals as $goal) {
            $api->addGoal($this->idSite, $goal['name'], $goal['match'], $goal['pattern'], $goal['patternType'], $caseSensitive = false, $goal['revenue'], $allowMultipleConversionsPerVisit = false);
        }
    }
}
