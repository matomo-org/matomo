<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;

class PageTitle extends ActionDimension
{
    protected $columnName = 'idaction_name';
    protected $columnType = 'INTEGER(10) UNSIGNED';
    protected $type = self::TYPE_JOIN_ID;
    protected $segmentName = 'pageTitle';
    protected $nameSingular = 'Actions_ColumnPageName';
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin(Action::TYPE_PAGE_TITLE);
    }

}
