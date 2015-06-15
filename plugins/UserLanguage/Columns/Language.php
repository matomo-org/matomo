<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage\Columns;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Language extends VisitDimension
{
    protected $columnName = 'location_browser_lang';
    protected $columnType = 'VARCHAR(20) NOT NULL';

    public function getName()
    {
        return Piwik::translate('General_Language');
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

    protected function configureSegments()
    {
        $segment = new \Piwik\Plugin\Segment();
        $segment->setCategory('Visit Location');
        $segment->setSegment('languageCode');
        $segment->setName('General_Language');
        $segment->setAcceptedValues('de, fr, en-gb, zh-cn, etc.');
        $this->addSegment($segment);
    }
}
