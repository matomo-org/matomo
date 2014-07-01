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

class ExitPageUrl extends VisitDimension
{
    protected $columnName = 'visit_exit_idaction_url';
    protected $columnType = 'INTEGER(11) UNSIGNED NULL DEFAULT 0';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setName('Actions_ColumnExitPageURL');
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
        $idActionUrl = false;

        if (!empty($action)) {
            $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();
        }

        return (int) $idActionUrl;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (empty($action)) {
            return false;
        }

        $id = $action->getIdActionUrlForEntryAndExitIds();

        if (!empty($id)) {
            $id = (int) $id;
        }

        return $id;
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnExitPageURL');
    }
}
