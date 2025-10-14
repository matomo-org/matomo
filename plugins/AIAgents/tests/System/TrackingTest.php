<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\AIAgents\Providers\ChatGPT as ChatGPTAgent;
use Piwik\Plugins\AIAgents\tests\Fixtures\AIAgents;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group AIAgents
 * @group AIAgentsTracking
 * @group Plugins
 */
class TrackingTest extends SystemTestCase
{
    /**
     * @var AIAgents
     */
    public static $fixture = null; // initialized below class definition

    public function testAIAgentNameTracked(): void
    {
        $actual = Db::get()->fetchAll('
            SELECT `ai_agent_name`, COUNT(DISTINCT `idvisitor`) AS `visitor_count`, COUNT(`idvisit`) AS `visit_count`
            FROM `' . Common::prefixTable('log_visit') . '`
            GROUP BY `ai_agent_name`
        ');

        $expected = [
            [
                'ai_agent_name' => null,
                'visitor_count' => 3,
                'visit_count'   => 4,
            ],
            [
                'ai_agent_name' => ChatGPTAgent::getInstance()->getId(),
                'visitor_count' => 5,
                'visit_count'   => 9,
            ],
        ];

        self::assertEquals($expected, $actual);
    }
}

TrackingTest::$fixture = new AIAgents();
