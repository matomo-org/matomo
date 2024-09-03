<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Referrers\DataTable\Filter\GroupDifferentSocialWritings;

/**
 * @group DataTableTest
 * @group GroupDifferentSocialWritingsTest
 * @group Core
 * @group sort
 */
class GroupDifferentSocialWritingsTest extends \PHPUnit\Framework\TestCase
{
    public function testRowsAreGrouped()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
                                      array(Row::COLUMNS => array('label' => 'instagram', 'count' => 100)),
                                      array(Row::COLUMNS => array('label' => 'Facebook', 'count' => 5)),
                                      array(Row::COLUMNS => array('label' => 'Instagram', 'count' => 10)
                                      )));
        $filter = new GroupDifferentSocialWritings($table);
        $filter->filter($table);
        $this->assertEquals(['Instagram', 'Facebook'], $table->getColumn('label'));
        $this->assertEquals([110, 5], $table->getColumn('count'));
    }
}
