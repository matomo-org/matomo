<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\Unit;

use Piwik\DataTable\Row;
use Piwik\Plugins\VisitsSummary\MajorityProfilable;
use PHPUnit\Framework\TestCase;

class MajorityProfilableTest extends TestCase
{
    /**
     * @dataProvider getTestDataForIsPeriodMajorityProfitable
     * @return
     */
    public function test_isPeriodMajorityProfilable_calculateIsProfilable($rowColumns, $expected)
    {
        $profilable = new MajorityProfilable();

        $row = new Row([Row::COLUMNS => $rowColumns]);
        $actual = $profilable->calculateIsProfilable($row);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForIsPeriodMajorityProfitable()
    {
        return [
            // all profilable
            [
                ['nb_visits' => 120000, 'nb_profilable' => 120000],
                true,
            ],

            // no profilable
            [
                ['nb_visits' => 120000, 'nb_profilable' => 0],
                false,
            ],

            // half profilable
            [
                ['nb_visits' => 120000, 'nb_profilable' => 60000],
                true,
            ],

            // 1% exactly profilable
            [
                ['nb_visits' => 1200000, 'nb_profilable' => 12000],
                true,
            ],

            // slightly less than 1% profilable
            [
                ['nb_visits' => 1200000, 'nb_profilable' => 11999],
                false,
            ],

            // slightly more than 1% profilable
            [
                ['nb_visits' => 1200000, 'nb_profilable' => 12001],
                true,
            ],

            // bad data 1
            [
                ['nb_visits' => 1200000, 'nb_profilable' => -10],
                false,
            ],

            // bad data 2
            [
                ['nb_visits' => 120000, 'nb_profilable' => 500000],
                true,
            ],

            // bad data 3
            [
                ['nb_visits' => 12000],
                true,
            ],
        ];
    }
}