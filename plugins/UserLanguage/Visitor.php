<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getLanguageCode()
    {
        return $this->details['location_browser_lang'];
    }

    public function getLanguage()
    {
        return languageTranslate($this->details['location_browser_lang']);
    }
}