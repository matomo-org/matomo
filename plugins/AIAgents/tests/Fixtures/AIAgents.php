<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\tests\Fixtures;

use MatomoTracker;
use Piwik\Config;
use Piwik\Date;
use Piwik\Plugins\AIAgents\API;
use Piwik\Plugins\AIAgents\Providers\ChatGPT as ChatGPTAgent;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Tests\Framework\Fixture;

class AIAgents extends Fixture
{
    public const SEGMENT_AI_AGENT_NAME_CHATGPT = 'aiAgentName==ChatGPT';

    public $dateTime = '2025-07-18 00:00:00';
    public $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpWebsite();
        $this->setUpSegments();
        $this->trackVisits();
    }

    private function setUpSegments(): void
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

        SegmentEditorAPI::getInstance()->add(
            'AI Agent Visitors',
            API::AI_AGENT_SEGMENT,
            $this->idSite
        );

        SegmentEditorAPI::getInstance()->add(
            'Human Visitors',
            API::HUMAN_SEGMENT,
            $this->idSite
        );

        SegmentEditorAPI::getInstance()->add(
            'AI Agent Name ChatGPT',
            self::SEGMENT_AI_AGENT_NAME_CHATGPT,
            $this->idSite
        );

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
    }

    private function setUpWebsite(): void
    {
        $this->idSite = self::createWebsite($this->dateTime);
    }

    private function trackVisits(): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime);
        $t->enableBulkTracking();

        $baseDate = Date::factory($this->dateTime);
        $visitorNum = 0;

        // Humans
        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackHumanVisit($t, $visitorNum, 1, $baseDate);

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackHumanVisit($t, $visitorNum, 2, $baseDate->addDay(1));
        $this->trackHumanVisit($t, $visitorNum, 2, $baseDate->addDay(1)->addHour(1));

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackHumanVisit($t, $visitorNum, 2, $baseDate->addDay(2));

        // ChatGPT Agents
        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackChatGPTAgentVisit($t, $visitorNum, 1, $baseDate);

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackChatGPTAgentVisit($t, $visitorNum, 1, $baseDate->addHour(1));

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackChatGPTAgentVisit($t, $visitorNum, 2, $baseDate->addDay(1));
        $this->trackChatGPTAgentVisit($t, $visitorNum, 2, $baseDate->addDay(1)->addHour(1));

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackChatGPTAgentVisit($t, $visitorNum, 2, $baseDate->addDay(1)->addHour(2));
        $this->trackChatGPTAgentVisit($t, $visitorNum, 2, $baseDate->addDay(1)->addHour(3));

        $visitorNum++;
        $t->setNewVisitorId();
        $this->trackChatGPTAgentVisit($t, $visitorNum, 1, $baseDate->addDay(2));
        $this->trackChatGPTAgentVisit($t, $visitorNum, 2, $baseDate->addDay(2)->addHour(1));
        $this->trackChatGPTAgentVisit($t, $visitorNum, 3, $baseDate->addDay(2)->addHour(2));

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }

    private function trackChatGPTAgentVisit(
        MatomoTracker $t,
        int $visitorNum,
        int $numberOfPageviews,
        Date $baseDate
    ): void {
        $t->setForceNewVisit();

        for ($i = 1; $i <= $numberOfPageviews; $i++) {
            $t->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE, 'value irrelevant');
            $t->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE_AGENT, '"https://chatgpt.com"');
            $t->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE_INPUT, 'value irrelevant');

            $t->setIp('127.0.0.1');
            $t->setForceVisitDateTime($baseDate->addHour($i * 0.1)->getDatetime());
            $t->setUrl("http://www.example.org/chatgpt-agent/$visitorNum/$i");
            $t->doTrackPageView("ChatGPT Agent - $visitorNum - $i");
        }
    }

    private function trackHumanVisit(
        MatomoTracker $t,
        int $visitorNum,
        int $numberOfPageviews,
        Date $baseDate
    ): void {
        $t->setForceNewVisit();

        for ($i = 1; $i <= $numberOfPageviews; $i++) {
            $t->setIp('192.168.0.1');
            $t->setForceVisitDateTime($baseDate->addHour($i * 0.1)->getDatetime());
            $t->setUrl("http://www.example.org/human/$visitorNum/$i");
            $t->doTrackPageView("Human - $visitorNum - $i");
        }
    }
}
