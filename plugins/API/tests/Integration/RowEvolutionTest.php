<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Plugins\API\RowEvolution;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group RowEvolutionTest
 * @group Plugins
 */
class RowEvolutionTest extends IntegrationTestCase
{

    public function setUp()
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Reports like VisitsSummary.get which do not have a dimension are not supported by row evolution
     */
    public function test_getRowEvolution_shouldTriggerAnException_IfReportHasNoDimension()
    {
        $rowEvolution = new RowEvolution();
        $rowEvolution->getRowEvolution(1, 'day', 'last7', 'VisitsSummary', 'get');
    }

    public function test_getRowEvolution_shouldNotTriggerAnException_IfReportHasADimension()
    {
        $rowEvolution = new RowEvolution();
        $table = $rowEvolution->getRowEvolution(1, 'day', 'last7', 'Actions', 'getPageUrls');
        $this->assertNotEmpty($table);
    }

}
