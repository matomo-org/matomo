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
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Site;
use Piwik\Widget\WidgetConfig;

class SingleMetricView extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        parent::configure($config);

        $column = Common::getRequestVar('column', 'nb_visits', 'string');

        $translations = self::getApiGetMetricTranslations();
        $columnTranslation = isset($translations[$column]) ? $translations[$column] : $column;

        $config->addParameters(['column' => $column]);
        $config->setCategoryId(Piwik::translate('General_General'));
        $config->setName($columnTranslation);
        $config->setIsWidgetizable();
        $config->setIsReusable();
    }

    public function render()
    {
        $metricName = Common::sanitizeInputValue(Common::getRequestVar('column', $default = null, 'string'));

        $metricTranslations = json_encode(self::getApiGetMetricTranslations());
        $metricTranslations = Common::sanitizeInputValue($metricTranslations);

        $period = Common::getRequestVar('period');
        $endDate = Common::getRequestVar('date', 'yesterday', 'string');

        $idSite = Common::getRequestVar('idSite');

        $sparklineRange = Range::getRelativeToEndDate($period, 'last30', $endDate, new Site($idSite));
        $sparklineRange = Common::sanitizeInputValue($sparklineRange);

        return "<piwik-single-metric-view metric=\"'$metricName'\" metric-translations=\"$metricTranslations\" sparkline-range=\"'$sparklineRange'\" />";
    }

    private static function getApiGetMetricTranslations()
    {
        $report = ReportsProvider::factory('API', 'get');
        return $report->getMetrics();
    }
}