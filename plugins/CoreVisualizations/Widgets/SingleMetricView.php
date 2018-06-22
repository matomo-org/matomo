<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Widgets;


use Piwik\Common;
use Piwik\Widget\WidgetConfig;

class SingleMetricView extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        parent::configure($config);

        $config->addParameters([
            'column' => Common::getRequestVar('column', 'nb_visits', 'string'),
        ]);
        $config->setCategoryId('Generic'); // TODO: translate
        $config->setName('Single Metric View'); // TODO: translate
        $config->setIsWidgetizable();
    }

    public function render()
    {
        $metricName = Common::getRequestVar('column', $default = null, 'string');
        return "<piwik-single-metric-view metric-name=\"$metricName\" />";
    }
}