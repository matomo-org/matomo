<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugins\Actions\Segment;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class ExitPageTitle extends VisitDimension
{
    protected $columnName = 'visit_exit_idaction_name';
    protected $columnType = 'INTEGER(11) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('exitPageTitle');
        $segment->setName('Actions_ColumnExitPageTitle');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int|bool
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $idActionName = false;

        if (!empty($action)) {
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
        }

        return (int) $idActionName;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int|bool
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (empty($action)) {
            return false;
        }

        return $action->getIdActionNameForEntryAndExitIds();
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnExitPageTitle');
    }
}
