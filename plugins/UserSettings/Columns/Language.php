<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

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
        $language = $request->getBrowserLanguage();

        if (empty($language)) {
            return '';
        }

        return substr($language, 0, 20);
    }
}