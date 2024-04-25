<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Unit\DataTable\Filter;

use Piwik\DataTable;
use Piwik\Plugins\Referrers\DataTable\Filter\UrlsFromWebsiteId;

require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

class UrlsFromWebsiteIdTest extends \PHPUnit\Framework\TestCase
{
    public function testFilterIgnoresDomainsPortssAndPortsInRecord()
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ['label' => 'https://abc.com/x/y/z', 'nb_visits' => 1],
            ['label' => 'https://def.com/x/y/z', 'nb_visits' => 1],
            ['label' => 'https://def.com/x/y/z/', 'nb_visits' => 1],
            ['label' => 'http://abc.com:3000/x/y/z', 'nb_visits' => 1],
            ['label' => 'http://abc.com/', 'nb_visits' => 1],
        ]);

        $filter = new UrlsFromWebsiteId($table);
        $filter->filter($table);

        $expected = [
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'x/y/z', 'nb_visits' => 3],
                DataTable\Row::METADATA => ['url' => ''],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'x/y/z/', 'nb_visits' => 1],
                DataTable\Row::METADATA => ['url' => ''],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'index', 'nb_visits' => 1],
                DataTable\Row::METADATA => ['url' => ''],
            ]),
        ];
        $this->assertEquals($expected, array_values($table->getRows()));
    }
}
