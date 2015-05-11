<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Widgets;

use Piwik\Common;
use Piwik\Site;

class GetEcommerceLog extends \Piwik\Plugin\Widget
{
    protected $category = 'Goals_Ecommerce';
    protected $name = 'Goals_EcommerceLog';

    public function isEnabled()
    {
        $idSite = $this->getIdSite();

        $site = new Site($idSite);
        return $site->isEcommerceEnabled();
    }

    private function getIdSite()
    {
        return Common::getRequestVar('idSite', null, 'int');
    }

}
