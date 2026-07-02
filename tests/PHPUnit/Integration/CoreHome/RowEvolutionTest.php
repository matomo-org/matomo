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
use Piwik\View;

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

    /**
     * A plugin whose report configureView adds an identifier to
     * request_parameters_to_modify (as plugin-CustomReports does with
     * idCustomReport) must still see that identifier reach the export URL
     * even when RowEvolution has populated export_parameters_to_modify['label'].
     * The _dataTableActions.twig template must merge the two maps, not pick one.
     */
    public function testDataTableActionsTemplateMergesRequestAndExportParameters(): void
    {
        $view = new View('@CoreHome/_dataTableActions');
        $view->properties = [
            // Simulates a plugin's configureView contribution.
            'request_parameters_to_modify' => ['idDummyReport' => 42, 'label' => ''],
            // Simulates RowEvolution::getRowEvolutionGraph overriding the label for exports.
            'export_parameters_to_modify'  => ['label' => '@rowLabel'],
            'show_footer'                  => true,
            'show_footer_icons'            => true,
            'title'                        => '',
            'apiMethodToRequestDataTable'  => 'Referrers.getWebsites',
            'max_export_filter_limit'      => 100,
            'show_export'                  => true,
            'show_export_as_image_icon'    => false,
            'hide_annotations_view'        => true,
            'show_search'                  => false,
            'report_id'                    => 'test',
            'show_flatten_table'           => false,
            'report_supports_flatten'      => false,
            'show_flatten_table_export'    => false,
            'show_periods'                 => false,
            'translations'                 => [],
        ];
        $view->footerIcons = [];
        $view->clientSideParameters = ['viewDataTable' => 'table'];
        $view->hasMultipleDimensions = false;
        $view->isDataTableEmpty = false;
        $view->isComparing = false;

        $html = $view->render();

        $this->assertSame(1, preg_match('/request-params="([^"]*)"/', $html, $matches));
        $requestParams = json_decode(htmlspecialchars_decode($matches[1]), true);

        $this->assertIsArray($requestParams);
        $this->assertSame(42, $requestParams['idDummyReport']);
        $this->assertSame('@rowLabel', $requestParams['label']);
    }

    private function setProperty(object $instance, string $propertyName, $value): void
    {
        $property = new \ReflectionProperty($instance, $propertyName);
        $property->setAccessible(true);
        $property->setValue($instance, $value);
    }
}
