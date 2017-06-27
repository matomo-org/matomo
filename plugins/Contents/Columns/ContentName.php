<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Columns;

use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ContentName extends ActionDimension
{
    protected $columnName = 'idaction_content_name';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $segmentName = 'contentName';
    protected $nameSingular = 'Contents_ContentName';
    protected $acceptValues = 'The name of a content block, for instance "Ad Sale"';
    protected $type = self::TYPE_JOIN_ID;
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin($this->getActionId());
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_NAME;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        $contentName = $request->getParam('c_n');

        if (empty($contentName)) {
            return false;
        }

        $contentName = trim($contentName);

        if (strlen($contentName) > 0) {
            return $contentName;
        }

        return false;
    }
}