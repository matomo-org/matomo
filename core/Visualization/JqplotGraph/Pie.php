<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Visualization\JqplotGraph;

use Piwik\Visualization\JqplotGraph;
use Piwik\JqplotDataGenerator;

/**
 * TODO
 */
class Pie extends JqplotGraph
{
    const ID = 'graphPie';

    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['visualization_properties']['max_graph_elements'] = 6;
        $result['visualization_properties']['allow_multi_select_series_picker'] = false;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties);
    }
}