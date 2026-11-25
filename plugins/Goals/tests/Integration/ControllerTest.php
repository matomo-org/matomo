<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group GoalsController
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private $idSite;
    private $idGoal;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00');
        $this->idGoal = Request::processRequest('Goals.addGoal', [
            'idSite' => $this->idSite,
            'name' => 'Test goal',
            'matchAttribute' => 'manually',
            'pattern' => 'x',
            'patternType' => 'contains',
        ]);
    }

    public function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function testGoalConversionsOverviewSegmentedVisitorLogLinkWithoutSegmentUsesGoalSegment()
    {
        $html = $this->renderGoalConversionsOverview([]);

        $this->assertStringContainsString(
            "SegmentedVisitorLog.show('Goals.getMetrics', 'visitConvertedGoalId==" . $this->idGoal . "', {})",
            $html
        );
    }

    public function testGoalConversionsOverviewSegmentedVisitorLogLinkKeepsSelectedSegment()
    {
        $html = $this->renderGoalConversionsOverview(['segment' => 'browserCode==FF']);

        $this->assertStringContainsString(
            "SegmentedVisitorLog.show('Goals.getMetrics', 'browserCode\\u003D\\u003DFF', {intersectSegment: 'visitConvertedGoalId==" . $this->idGoal . "'})",
            $html
        );
    }

    public function testGoalConversionsOverviewSegmentedVisitorLogLinkEscapesSelectedSegment()
    {
        $html = $this->renderGoalConversionsOverview(['segment' => "pageTitle==a'\"<b"]);

        $this->assertStringContainsString("{intersectSegment: 'visitConvertedGoalId==" . $this->idGoal . "'})", $html);
        $this->assertStringContainsString("SegmentedVisitorLog.show('Goals.getMetrics', 'pageTitle\\u003D\\u003Da\\u0027\\u0022\\u003Cb', {", $html);
        $this->assertStringNotContainsString("a'\"<b", $html);
    }

    private function renderGoalConversionsOverview(array $params)
    {
        $_GET = array_merge([
            'module' => 'Goals',
            'action' => 'goalConversionsOverview',
            'idSite' => (string) $this->idSite,
            'period' => 'day',
            'date' => '2024-01-02',
            'idGoal' => (string) $this->idGoal,
        ], $params);

        return Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Goals', 'goalConversionsOverview');
        });
    }
}
