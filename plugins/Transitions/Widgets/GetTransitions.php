<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Transitions\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetTransitions extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Actions');
        $config->setSubcategoryId('Transitions_Transitions');
        $config->setName('Transitions_Transitions');
        $config->setOrder(99);
        $idSite = self::getIdSite();
        if (!$idSite || !Piwik::isUserHasViewAccess($idSite)) {
            $config->disable();
        }
    }

    private static function getIdSite()
    {
        return Common::getRequestVar('idSite', 0, 'int');
    }

    public function render()
    {
        Piwik::checkUserHasViewAccess(self::getIdSite());

        $isWidgetized = Common::getRequestVar('widget', 0, 'int') === 1;

        return $this->renderTemplate('transitions', array(
            'isWidget' => $isWidgetized
        ));
    }

}