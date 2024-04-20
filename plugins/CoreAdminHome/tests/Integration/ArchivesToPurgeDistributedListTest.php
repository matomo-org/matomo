<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class ArchivesToPurgeDistributedListTest extends IntegrationTestCase
{
    public function test_construct_CorrectlyConvertsOldListValues()
    {
        $oldItems = array(
            '2015_01' => array(1,2,3),
            '2013_02' => array(3),
            3 => '2015_03',
            '2014_01' => array(),
            4 => '2015_06'
        );
        Option::set(ArchivesToPurgeDistributedList::OPTION_INVALIDATED_DATES_SITES_TO_PURGE, serialize($oldItems));

        $list = new ArchivesToPurgeDistributedList();
        $items = $list->getAll();

        $expected = array('2015_03', '2015_06', '2015_01', '2013_02', '2014_01');
        $this->assertEquals($expected, array_values($items));
    }
}
