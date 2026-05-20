<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CoreHome;

use Piwik\DataTable\Map;
use Piwik\Plugins\CoreHome\DataTableRowAction\RowEvolution;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group RowEvolution
 */
class RowEvolutionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        $_GET['idSite'] = 1;
        $_GET['date'] = '2014-01-01';
        $_GET['period'] = 'day';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['date'], $_GET['period']);
        parent::tearDown();
    }

    public function testGetRowEvolutionGraphKeepsGraphLabelResetSeparateFromExportLabel(): void
    {
        $rowEvolution = (new \ReflectionClass(RowEvolution::class))->newInstanceWithoutConstructor();

        $this->setProperty($rowEvolution, 'apiMethod', 'Referrers.getWebsites');
        $this->setProperty($rowEvolution, 'label', '@referrer.com');
        $this->setProperty($rowEvolution, 'graphType', 'graphEvolution');
        $this->setProperty($rowEvolution, 'dataTable', new Map());
        $this->setProperty($rowEvolution, 'availableMetrics', []);

        $view = $rowEvolution->getRowEvolutionGraph();

        $this->assertSame('', $view->requestConfig->request_parameters_to_modify['label']);
        $this->assertSame('@referrer.com', $view->config->export_parameters_to_modify['label']);
        $this->assertFalse($view->config->show_flatten_table_export);
    }

    private function setProperty(object $instance, string $propertyName, $value): void
    {
        $property = new \ReflectionProperty($instance, $propertyName);
        $property->setAccessible(true);
        $property->setValue($instance, $value);
    }
}
