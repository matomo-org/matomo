<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Widgets;

use Piwik\SettingsPiwik;

class GetKeywordsForPage extends \Piwik\Plugin\Widget
{
    protected $category = 'SEO';
    protected $name = 'Referrers_WidgetTopKeywordsForPages';

    public function isEnabled()
    {
        return SettingsPiwik::isSegmentationEnabled();
    }

}
