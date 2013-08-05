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

    protected function getDefaultPropertyValues($view)
    {
        $result = parent::getDefaultPropertyValues($view);
        $result['graph_limit'] = 6;
        $result['allow_multi_select_series_picker'] = false;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties);
    }
}