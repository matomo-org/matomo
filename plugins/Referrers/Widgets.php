<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\SettingsPiwik;
use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        if (SettingsPiwik::isSegmentationEnabled()) {
            $widgetsList->add('SEO', 'Referrers_WidgetTopKeywordsForPages', 'Referrers', 'getKeywordsForPage');
        }
    }

}
