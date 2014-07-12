<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Example Widgets';

    protected function init()
    {
        $this->addWidget('CoreHome_SupportPiwik', 'getDonateForm');
        $this->addWidget('Installation_Welcome', 'getPromoVideo');
    }

    /**
     * Renders and echo's the in-app donate form w/ slider.
     */
    public function getDonateForm()
    {
        $view = new View('@CoreHome/getDonateForm');

        if (Common::getRequestVar('widget', false)
            && Piwik::hasUserSuperUserAccess()) {
            $view->footerMessage = Piwik::translate('CoreHome_OnlyForSuperUserAccess');
        }

        return $view->render();
    }

    /**
     * Renders and echo's HTML that displays the Piwik promo video.
     */
    public function getPromoVideo()
    {
        $view = new View('@CoreHome/getPromoVideo');
        $view->shareText     = Piwik::translate('CoreHome_SharePiwikShort');
        $view->shareTextLong = Piwik::translate('CoreHome_SharePiwikLong');
        $view->promoVideoUrl = 'https://www.youtube.com/watch?v=OslfF_EH81g';

        return $view->render();
    }
}
