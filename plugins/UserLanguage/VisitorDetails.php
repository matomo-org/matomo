<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['languageCode'] = $this->getLanguageCode();
        $visitor['language']     = $this->getLanguage();
    }

    protected function getLanguageCode()
    {
        return $this->details['location_browser_lang'];
    }

    protected function getLanguage()
    {
        return languageTranslate($this->details['location_browser_lang']);
    }
}