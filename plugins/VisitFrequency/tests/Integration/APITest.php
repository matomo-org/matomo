<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitFrequency\tests;

use Piwik\DataTable\DataTableInterface;
use Piwik\Plugins\VisitFrequency\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class APITest extends IntegrationTestCase
{
    private $api;

    private $idSite;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        $this->idSite = Fixture::createWebsite('2018-12-01');
    }

    public function testNewMetricsOnly()
    {
        $columns = array('nb_visits_new', 'nb_actions_new');

        /** @var DataTableInterface $dataTable */
        $dataTable = $this->api->get($this->idSite, 'week', '2019-01-01', false, $columns);
        $this->assertEquals($dataTable->getRowsCount(), 1);
        $columnsReturned = array_keys($dataTable->getFirstRow()->getArrayCopy());

        $this->assertEquals($columns, $columnsReturned);
    }

    public function testReturningMetricsOnly()
    {
        $columns = array('nb_visits_returning', 'nb_uniq_visitors_returning');

        /** @var DataTableInterface $dataTable */
        $dataTable = $this->api->get($this->idSite, 'week', '2019-01-01', false, $columns);
        $this->assertEquals($dataTable->getRowsCount(), 1);
        $columnsReturned = array_keys($dataTable->getFirstRow()->getArrayCopy());

        $this->assertEquals($columns, $columnsReturned);
    }

    public function testNoNewOrReturningMetrics()
    {
        $columns = array('nb_visits');

        /** @var DataTableInterface $dataTable */
        $dataTable = $this->api->get($this->idSite, 'week', '2019-01-01', false, $columns);
        $this->assertEquals($dataTable->getRowsCount(), 0);
    }

    public function testDifferentNewAndReturningMetrics()
    {
        $columns = array('nb_visits_new', 'nb_actions_per_visit_new', 'nb_actions_returning', 'avg_time_on_site_returning');

        /** @var DataTableInterface $dataTable */
        $dataTable = $this->api->get($this->idSite, 'week', '2019-01-01', false, $columns);
        $this->assertEquals($dataTable->getRowsCount(), 1);
        $columnsReturned = array_keys($dataTable->getFirstRow()->getArrayCopy());

        $this->assertEquals($columns, $columnsReturned);
    }

    public function testNoColumnsPassed()
    {
        /** @var DataTableInterface $dataTable */
        $dataTable = $this->api->get($this->idSite, 'week', '2019-01-01');
        $this->assertEquals($dataTable->getRowsCount(), 1);
        $columnsReturned = array_keys($dataTable->getFirstRow()->getArrayCopy());

        $this->assertCount(22, $columnsReturned);
    }
}
