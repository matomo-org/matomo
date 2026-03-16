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
}
