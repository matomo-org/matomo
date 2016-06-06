<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Menu\MenuUser;
use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\MobileMessaging\API as APIMobileMessaging;

class Menu extends \Piwik\Plugin\Menu
{
    const MOBILE_MESSAGING_TOP_MENU_TRANSLATION_KEY = 'MobileMessaging_TopMenu';
    const PDF_REPORTS_TOP_MENU_TRANSLATION_KEY = 'ScheduledReports_EmailReports';

    public function configureUserMenu(MenuUser $menu)
    {
        $tooltip = Piwik::translate(
            \Piwik\Plugin\Manager::getInstance()->isPluginActivated('MobileMessaging')
                ? 'MobileMessaging_TopLinkTooltip' : 'ScheduledReports_TopLinkTooltip');

        $menu->addPersonalItem(
            $this->getTopMenuTranslationKey(),
            $this->urlForAction('index', array('segment' => false)),
            7,
            $tooltip
        );
    }

    function getTopMenuTranslationKey()
    {
        // if MobileMessaging is not activated, display 'Email reports'
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MobileMessaging'))
            return self::PDF_REPORTS_TOP_MENU_TRANSLATION_KEY;

        if (Piwik::isUserIsAnonymous()) {
            return self::MOBILE_MESSAGING_TOP_MENU_TRANSLATION_KEY;
        }

        try {
            $reports = API::getInstance()->getReports();
            $reportCount = count($reports);

            // if there are no reports and the mobile account is
            //  - not configured: display 'Email reports'
            //  - configured: display 'Email & SMS reports'
            if ($reportCount == 0) {
                return APIMobileMessaging::getInstance()->areSMSAPICredentialProvided() ?
                    self::MOBILE_MESSAGING_TOP_MENU_TRANSLATION_KEY : self::PDF_REPORTS_TOP_MENU_TRANSLATION_KEY;
            }
        } catch(\Exception $e) {
            return self::PDF_REPORTS_TOP_MENU_TRANSLATION_KEY;
        }

        $anyMobileReport = false;
        foreach ($reports as $report) {
            if ($report['type'] == MobileMessaging::MOBILE_TYPE) {
                $anyMobileReport = true;
                break;
            }
        }

        // if there is at least one sms report, display 'Email & SMS reports'
        if ($anyMobileReport) {
            return self::MOBILE_MESSAGING_TOP_MENU_TRANSLATION_KEY;
        }

        return self::PDF_REPORTS_TOP_MENU_TRANSLATION_KEY;
    }

}
