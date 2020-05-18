<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Site;

class Controller extends \Piwik\Plugin\Controller
{
    public function manage()
    {
        $this->checkSitePermission();
        Piwik::checkUserHasAdminAccess($this->idSite);

        return $this->renderTemplate('manage', array());
    }

}

