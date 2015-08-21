<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Widgets;

use Piwik\Piwik;
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
    }

    public function render()
    {

    }

}
