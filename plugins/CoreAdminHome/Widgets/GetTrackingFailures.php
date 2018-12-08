<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome\Widgets;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetTrackingFailures extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('CoreAdminHome_TrackingFailures');
        $config->setOrder(5);

        if (!Piwik::isUserHasSomeAdminAccess()) {
            $config->disable();
        }
    }

    public function render()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $failures = Request::processRequest('CoreAdminHome.getTrackingFailures');
        $numFailures = count($failures);

        return $this->renderTemplate('getTrackingFailures', array(
            'numFailures' => $numFailures
        ));
    }
}