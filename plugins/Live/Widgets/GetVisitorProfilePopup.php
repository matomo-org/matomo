<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Live\Live;
use Piwik\Widget\WidgetConfig;

class GetVisitorProfilePopup extends \Piwik\Widget\Widget
{

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setName('Live_VisitorProfile');
        $config->setOrder(25);

        if (Piwik::isUserIsAnonymous()) {
            $config->disable();
        }

        $idSite = Common::getRequestVar('idSite', 0, 'int');

        if (empty($idSite)) {
            return;
        }

        if (!Live::isVisitorProfileEnabled($idSite)) {
            $config->disable();
        }
    }

    public function render()
    {

    }

}
