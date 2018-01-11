<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage;

use Piwik\Piwik;
use Piwik\FrontController;

/**
 *
 */
class UserLanguage extends \Piwik\Plugin
{
    public function postLoad()
    {
        Piwik::addAction('Template.footerUserCountry', array('Piwik\Plugins\UserLanguage\UserLanguage', 'footerUserCountry'));
    }

    public static function footerUserCountry(&$out)
    {
        $out .= '<h2 piwik-enriched-headline>' . Piwik::translate('UserLanguage_BrowserLanguage') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('UserLanguage', 'getLanguage');
    }
}
