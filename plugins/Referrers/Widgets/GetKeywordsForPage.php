<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Widgets;

use Piwik\Widget\WidgetConfig;
use Piwik\SettingsPiwik;

class GetKeywordsForPage extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('SEO');
        $config->setName('Referrers_WidgetTopKeywordsForPages');
        $config->setIsEnabled(SettingsPiwik::isSegmentationEnabled());
    }

}
