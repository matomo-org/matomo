<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\DataTable\Filter\Actions as ActionsFilter;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Action;

require_once PIWIK_INCLUDE_PATH . '/plugins/Actions/Actions.php';

/**
 * @group Actions
 * @group DataTableFilterActionsTest
 * @group Plugins
 */
class DataTableFilterActionsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        SitesManagerAPI::unsetInstance();
    }

    public function testFilterSetsNullSegmentForUnknownPageUrlEvenWithoutSiteUrlPrefix()
    {
        ArchivingHelper::reloadConfig();

        $table = new DataTable();
        $notDefinedUrl = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_URL);
        $row = new Row([Row::COLUMNS => ['label' => $notDefinedUrl]]);
        $table->addRow($row);

        $filter = new ActionsFilter($table, Action::TYPE_PAGE_URL);
        $filter->filter($table);

        $allMetadata = $row->getMetadata();
        $this->assertArrayHasKey('segment', $allMetadata);
        $this->assertNull($allMetadata['segment']);
    }

    public function testFilterEmitsOrJoinedSegmentForFolderRowOnSiteWithAliases()
    {
        ArchivingHelper::reloadConfig();

        // Site stub: main_url + getId.
        $site = $this->createMock(Site::class);
        $site->method('getId')->willReturn(1);
        $site->method('getMainUrl')->willReturn('https://main.example.com');

        // SitesManager API stub returning main + one alias.
        $sitesManager = $this->getMockBuilder(SitesManagerAPI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSiteUrlsFromId'])
            ->getMock();
        $sitesManager->method('getSiteUrlsFromId')->willReturn([
            'https://main.example.com',
            'https://alias.example.com',
        ]);
        SitesManagerAPI::setSingletonInstance($sitesManager);

        $table = new DataTable();
        $table->setMetadata('site', $site);
        $row = new Row([Row::COLUMNS => ['label' => 'products']]);
        $row->setMetadata('folder_url_start', 'https://main.example.com/products');
        $table->addRow($row);

        (new ActionsFilter($table, Action::TYPE_PAGE_URL))->filter($table);

        $segment = $row->getMetadata('segment');
        $this->assertIsString($segment);

        $mainClause = 'pageUrl=^' . urlencode(urlencode('https://main.example.com/products'));
        $aliasClause = 'pageUrl=^' . urlencode(urlencode('https://alias.example.com/products'));

        $this->assertStringContainsString($mainClause, $segment);
        $this->assertStringContainsString($aliasClause, $segment);
        $this->assertSame($mainClause . ',' . $aliasClause, $segment);
    }

    public function testFilterFallsBackToSingleClauseWhenSiteMetadataIsMissing()
    {
        ArchivingHelper::reloadConfig();

        $table = new DataTable();
        $row = new Row([Row::COLUMNS => ['label' => 'products']]);
        $row->setMetadata('folder_url_start', 'https://main.example.com/products');
        $table->addRow($row);

        (new ActionsFilter($table, Action::TYPE_PAGE_URL))->filter($table);

        $expected = 'pageUrl=^' . urlencode(urlencode('https://main.example.com/products'));
        $this->assertSame($expected, $row->getMetadata('segment'));
    }
}
