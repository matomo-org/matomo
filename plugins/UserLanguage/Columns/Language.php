<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage\Columns;

use Piwik\Common;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

class Language extends VisitDimension
{
    protected $columnName = 'location_browser_lang';
    protected $columnType = 'VARCHAR(20) NULL';
    protected $category = 'UserCountry_VisitLocation';
    protected $nameSingular = 'General_Language';
    protected $namePlural = 'General_Languages';
    protected $segmentName = 'languageCode';
    protected $acceptValues = 'de, fr, en-gb, zh-cn, etc.';
    protected $type = self::TYPE_TEXT;

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\UserLanguage\languageTranslateWithCode($value);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getSingleLanguageFromAcceptedLanguages($request->getBrowserLanguage());
    }

    /**
     * For better privacy we store only the main language code, instead of the whole browser language string.
     * 
     * @param $acceptLanguagesString
     * @return string
     */
    protected function getSingleLanguageFromAcceptedLanguages($acceptLanguagesString)
    {
        if (empty($acceptLanguagesString)) {
            return '';
        }

        $languageCode = Common::extractLanguageAndRegionCodeFromBrowserLanguage($acceptLanguagesString);
        return $languageCode;
    }

}
