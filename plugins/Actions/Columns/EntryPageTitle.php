<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class EntryPageTitle extends VisitDimension
{
    protected $columnName = 'visit_entry_idaction_name';
    protected $columnType = 'INTEGER(10) UNSIGNED NULL';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'entryPageTitle';
    protected $suggestedValuesApi = 'Actions.getEntryPageTitles';
    protected $nameSingular = 'Actions_ColumnEntryPageTitle';
    protected $namePlural = 'Actions_WidgetEntryPageTitles';
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_TITLE);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $idActionName = false;

        if (!empty($action)) {
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
        }

        return (int) $idActionName;
    }
}
