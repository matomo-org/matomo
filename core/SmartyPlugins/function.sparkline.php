<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * @param array $params  array([src] => src url of the image)
 * @param bool $smarty
 * @return string IMG HTML tag
 */
function smarty_function_sparkline($params, &$smarty = false)
{
    $src = $params['src'];
    $graph = new Piwik_Visualization_Sparkline();
    $width = $graph->getWidth();
    $height = $graph->getHeight();
    return "<img class=\"sparkline\" alt=\"\" src=\"$src\" width=\"$width\" height=\"$height\" />";
}
