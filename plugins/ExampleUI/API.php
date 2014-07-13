<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleUI;

use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Range;

/**
 * ExampleUI API is also an example API useful if you are developing a Piwik plugin.
 *
 * The functions listed in this API are returning the data used in the Controller to draw graphs and
 * display tables. See also the ExampleAPI plugin for an introduction to Piwik APIs.
 *
 * @method static \Piwik\Plugins\ExampleUI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public static $disableRandomness = false;

    public function getTemperaturesEvolution($date, $period)
    {
        $temperatures = array();

        $date   = Date::factory('2013-10-10', 'UTC');
        $period = new Range($period, 'last30');
        $period->setDefaultEndDate($date);

        foreach ($period->getSubperiods() as $subPeriod) {
            if (self::$disableRandomness) {
                $server1 = 50;
                $server2 = 40;
            } else {
                $server1 = mt_rand(50, 90);
                $server2 = mt_rand(40, 110);
            }

            $value = array('server1' => $server1, 'server2' => $server2);

            $temperatures[$subPeriod->getLocalizedShortString()] = $value;
        }

        return DataTable::makeFromIndexedArray($temperatures);
    }

    public function getTemperatures()
    {
        $xAxis = array(
            '0h', '1h', '2h', '3h', '4h', '5h', '6h', '7h', '8h', '9h', '10h', '11h',
            '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h',
        );

        $temperatureValues = array_slice(range(50, 90), 0, count($xAxis));
        if (!self::$disableRandomness) {
            shuffle($temperatureValues);
        }

        $temperatures = array();
        foreach ($xAxis as $i => $xAxisLabel) {
            $temperatures[$xAxisLabel] = $temperatureValues[$i];
        }

        return DataTable::makeFromIndexedArray($temperatures);
    }

    public function getPlanetRatios()
    {
        $planetRatios = array(
            'Mercury' => 0.382,
            'Venus'   => 0.949,
            'Earth'   => 1.00,
            'Mars'    => 0.532,
            'Jupiter' => 11.209,
            'Saturn'  => 9.449,
            'Uranus'  => 4.007,
            'Neptune' => 3.883,
        );

        return DataTable::makeFromIndexedArray($planetRatios);
    }

    public function getPlanetRatiosWithLogos()
    {
        $planetsDataTable = $this->getPlanetRatios();

        foreach ($planetsDataTable->getRows() as $row) {
            $logo = sprintf('plugins/ExampleUI/images/icons-planet/%s.png', strtolower($row->getColumn('label')));
            $url = sprintf('http://en.wikipedia.org/wiki/%s', $row->getColumn('label'));

            $row->addMetadata('logo', $logo);
            $row->addMetadata('url', $url);
        }

        return $planetsDataTable;
    }
}
