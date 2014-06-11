<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugins\Actions\Segment;
use Piwik\Plugin\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ExitPageTitle extends VisitDimension
{
    protected $fieldName = 'visit_exit_idaction_name';
    protected $fieldType = 'INTEGER(11) UNSIGNED NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('exitPageTitle');
        $segment->setName('Actions_ColumnExitPageTitle');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param $visit
     * @param Action|null $action
     * @return bool
     */
    public function onNewVisit(Request $request, $visit, $action)
    {
        $idActionName = false;

        if (!empty($action)) {
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
        }

        return (int) $idActionName;
    }

    public function onExistingVisit(Request $request, $visit, $action)
    {
        if (!empty($action)) {
            return (int) $action->getIdActionNameForEntryAndExitIds();
        }

        return false;
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnExitPageTitle');
    }
}
