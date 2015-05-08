<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Widget;
use Piwik\Translation\Translator;
use Piwik\View;

class GetDonateForm extends Widget
{
    protected $category = 'Example Widgets';
    protected $name     = 'CoreHome_SupportPiwik';
    protected $order    = 5;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function render()
    {
        $view = new View('@CoreHome/getDonateForm');

        if (Common::getRequestVar('widget', false)
            && Piwik::hasUserSuperUserAccess()) {
            $view->footerMessage = $this->translator->translate('CoreHome_OnlyForSuperUserAccess');
        }

        return $view->render();
    }
}