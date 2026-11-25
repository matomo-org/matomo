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
use Piwik\Plugins\Actions\DataTable\Filter\PageUrlSearch;

/**
 * @group Actions
 * @group PageUrlSearchTest
 * @group Plugins
 */
class PageUrlSearchTest extends \PHPUnit\Framework\TestCase
{
    public function testSearchesTheLabelsOfATableWithoutItsSite()
    {
        $table = new DataTable();
        $table->addRowsFromArray([
            [Row::COLUMNS => ['label' => 'https://example.com/pricing', 'nb_visits' => 1]],
            [Row::COLUMNS => ['label' => '/pricing', 'nb_visits' => 1]],
        ]);

        $table->filter(PageUrlSearch::class, ['https://example.com/pricing']);

        $this->assertEquals(['https://example.com/pricing'], $table->getColumn('label'));
    }
}
