<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI;

use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Range;

/**
 * ExampleUI API is also an example API useful if you are developing a Matomo plugin.
 *
 * The functions listed in this API are returning the data used in the Controller to draw graphs and
 * display tables. See also the ExampleAPI plugin for an introduction to Matomo APIs.
 *
 * @method static \Piwik\Plugins\ExampleUI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public static bool $disableRandomness = false;

    public function getTemperaturesEvolution(string $date, string $period): DataTable
    {
        $temperatures = [];

        $endDate = Date::factory('2013-10-10', 'UTC');
        $range   = new Range($period, 'last30');
        $range->setDefaultEndDate($endDate);

        foreach ($range->getSubperiods() as $subPeriod) {
            if (self::$disableRandomness) {
                $server1 = 50;
                $server2 = 40;
            } else {
                $server1 = mt_rand(50, 90);
                $server2 = mt_rand(40, 110);
            }

            $temperatures[$subPeriod->getLocalizedShortString()] = ['server1' => $server1, 'server2' => $server2];
        }

        return DataTable::makeFromIndexedArray($temperatures);
    }

    public function getTemperatures(): DataTable
    {
        $xAxis = [
            '0h', '1h', '2h', '3h', '4h', '5h', '6h', '7h', '8h', '9h', '10h', '11h',
            '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h',
        ];

        $temperatureValues = array_slice(range(50, 90), 0, count($xAxis));
        if (!self::$disableRandomness) {
            shuffle($temperatureValues);
        }

        $temperatures = [];
        foreach ($xAxis as $i => $xAxisLabel) {
            $temperatures[$xAxisLabel] = $temperatureValues[$i];
        }

        return DataTable::makeFromIndexedArray($temperatures);
    }

    public function getPlanetRatios(): DataTable
    {
        $planetRatios = [
            'Mercury' => 0.382,
            'Venus'   => 0.949,
            'Earth'   => 1.00,
            'Mars'    => 0.532,
            'Jupiter' => 11.209,
            'Saturn'  => 9.449,
            'Uranus'  => 4.007,
            'Neptune' => 3.883,
        ];

        return DataTable::makeFromIndexedArray($planetRatios);
    }

    public function getPlanetRatiosWithLogos(): DataTable
    {
        $planetsDataTable = $this->getPlanetRatios();

        foreach ($planetsDataTable->getRows() as $row) {
            $label = $row->getColumn('label');

            if (!is_string($label)) {
                continue;
            }

            $row->addMetadata('logo', sprintf('plugins/ExampleUI/images/icons-planet/%s.png', strtolower($label)));
            $row->addMetadata('url', sprintf('http://en.wikipedia.org/wiki/%s', $label));
        }

        return $planetsDataTable;
    }
}
