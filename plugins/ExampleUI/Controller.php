<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleUI;

use Piwik\Notification;
use Piwik\View;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function notifications()
    {
        $notification = new Notification('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        Notification\Manager::notify('ExampleUI_InfoSimple', $notification);

        $notification = new Notification('Neque porro quisquam est qui dolorem ipsum quia dolor sit amet.');
        $notification->title   = 'Warning:';
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->flags   = null;
        Notification\Manager::notify('ExampleUI_warningWithClose', $notification);

        $notification = new Notification('Phasellus tincidunt arcu at justo faucibus, et lacinia est accumsan. ');
        $notification->title   = 'Well done';
        $notification->context = Notification::CONTEXT_SUCCESS;
        $notification->type    = Notification::TYPE_TOAST;
        Notification\Manager::notify('ExampleUI_successToast', $notification);

        $notification = new Notification('Phasellus tincidunt arcu at justo <a href="#">faucibus</a>, et lacinia est accumsan. ');
        $notification->raw     = true;
        $notification->context = Notification::CONTEXT_ERROR;
        Notification\Manager::notify('ExampleUI_error', $notification);

        $view = new View('@ExampleUI/notifications');
        $this->setGeneralVariablesView($view);
        return $view->render();
    }
}
