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
class Bar extends JqplotGraph
{
    const ID = 'graphVerticalBar';

    protected function getDefaultPropertyValues($view)
    {
        $result = parent::getDefaultPropertyValues($view);
        $result['graph_limit'] = 6;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('bar', $properties);
    }
}