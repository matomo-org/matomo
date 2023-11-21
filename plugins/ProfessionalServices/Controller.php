<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Common;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Request;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function dismissPromotion(): void
    {
        $pluginName =  Request::fromRequest()->getStringParameter('pluginName');

        // Todo: validate plugin name here.

        // Todo: Use correct notification message.
        $notification = new Notification('Disabled, thanks');
        Notification\Manager::notify('ExampleUI_InfoSimple', $notification);

        DismissOption::dismissPluginPromotionForUser($pluginName, Piwik::getCurrentUserLogin());

        $this->redirectToIndex('CoreHome', 'index');
    }
}
