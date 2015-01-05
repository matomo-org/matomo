<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Piwik\Piwik;

class Ecommerce extends \Piwik\Plugin
{
    public function getInformation()
    {
        $suffix = Piwik::translate('SitesManager_PiwikOffersEcommerceAnalytics',
            array('<a href="http://piwik.org/docs/ecommerce-analytics/" rel="noreferrer"  target="_blank">', '</a>'));
        $info = parent::getInformation();
        $info['description'] = $suffix;
        return $info;
    }

}
