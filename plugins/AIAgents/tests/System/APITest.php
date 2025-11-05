<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\tests\System;

use Piwik\Date;
use Piwik\Plugins\AIAgents\API;
use Piwik\Plugins\AIAgents\tests\Fixtures\AIAgents;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group AIAgents
 * @group AIAgentsAPI
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var AIAgents
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        return [
            [
                'AIAgents.get',
                [
                    'idSite'     => self::$fixture->idSite,
                    'date'       => Date::factory(self::$fixture->dateTime)->toString(),
                    'period'     => ['day', 'week', 'month'],
                ],
            ],
            [
                'VisitsSummary.get',
                [
                    'idSite'     => self::$fixture->idSite,
                    'date'       => Date::factory(self::$fixture->dateTime)->toString(),
                    'period'     => 'day',
                    'segment'    => API::HUMAN_SEGMENT,
                    'testSuffix' => '_human',
                ],
            ],
            [
                'VisitsSummary.get',
                [
                    'idSite'     => self::$fixture->idSite,
                    'date'       => Date::factory(self::$fixture->dateTime)->toString(),
                    'period'     => 'day',
                    'segment'    => API::AI_AGENT_SEGMENT,
                    'testSuffix' => '_aiAgent',
                ],
            ],
            [
                'VisitsSummary.get',
                [
                    'idSite'     => self::$fixture->idSite,
                    'date'       => Date::factory(self::$fixture->dateTime)->toString(),
                    'period'     => ['day', 'week', 'month'],
                    'segment'    => AIAgents::SEGMENT_AI_AGENT_NAME_CHATGPT,
                    'testSuffix' => '_aiAgentName_ChatGPT',
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

APITest::$fixture = new AIAgents();
