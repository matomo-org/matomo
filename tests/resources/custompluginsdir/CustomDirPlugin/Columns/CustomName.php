<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDirPlugin\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class CustomName extends VisitDimension
{
    protected $columnName = 'custom_name';

    protected $columnType = 'INTEGER(11) DEFAULT 0 NULL';

    protected $nameSingular = 'CustomDirPlugin_CustomName';

    protected $segmentName = 'customDirPlugin';

    protected $acceptValues = 'A plugin located in a different directory';

    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if (empty($action)) {
            return 'foo';
        }

        return 'bar';
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (empty($action)) {
            return false; // Do not change an already persisted value
        }

        return 'baz';
    }

    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }

}