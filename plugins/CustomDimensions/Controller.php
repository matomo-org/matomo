<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions;

use Piwik\Common;
use Piwik\Piwik;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function manage()
    {
        $idSite = Common::getRequestVar('idSite');

        Piwik::checkUserHasWriteAccess($idSite);

        return $this->renderTemplate('manage', array(
            'idSite' => $this->idSite,
            'title' => Piwik::translate('CustomDimensions_CustomDimensions'
        )));
    }

}

