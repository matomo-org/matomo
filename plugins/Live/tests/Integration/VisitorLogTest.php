<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\DataTable;
use Piwik\Plugins\Live\Visualizations\VisitorLog;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Live
 * @group VisitorLogTest
 * @group Plugins
 */
class VisitorLogTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        unset($_GET['intersectSegment']);
        parent::tearDown();
    }

    public function testExportKeepsTheRowFilterOfASegmentedVisitsLog()
    {
        $_GET['intersectSegment'] = 'pageUrl==https%3A%2F%2Fexample.org%2Fblog%2F';

        $visitorLog = $this->renderVisitorLog();

        $this->assertSame(
            ['intersectSegment' => 'pageUrl==https%3A%2F%2Fexample.org%2Fblog%2F'],
            $visitorLog->config->export_parameters_to_modify
        );
    }

    public function testExportHasNoRowFilterWhenTheVisitsLogHasNone()
    {
        $visitorLog = $this->renderVisitorLog();

        $this->assertSame([], $visitorLog->config->export_parameters_to_modify);
    }

    private function renderVisitorLog(): VisitorLog
    {
        $visitorLog = new VisitorLog('Live.getLastVisitsDetails', 'Live.getLastVisitsDetails');
        $visitorLog->setDataTable(new DataTable());
        $visitorLog->beforeRender();

        return $visitorLog;
    }
}
