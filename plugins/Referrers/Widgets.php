<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\SettingsPiwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'SEO';

    public function init()
    {
        if (SettingsPiwik::isSegmentationEnabled()) {
            $this->addWidget('Referrers_WidgetTopKeywordsForPages', 'getKeywordsForPage');
        }
    }

}
