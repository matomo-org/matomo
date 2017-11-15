<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;
use Piwik\SettingsPiwik;
use Piwik\Widget\WidgetsList;

/**
 */
class SEO extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Widget.filterWidgets' => 'filterWidgets'
        ];
    }

    /**
     * @param WidgetsList $list
     */
    public function filterWidgets($list)
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            $list->remove('SEO', 'SEO_SeoRankings');
        }
    }
}
