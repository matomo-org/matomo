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

class ExitPageUrl extends VisitDimension
{
    protected $fieldName = 'visit_exit_idaction_url';
    protected $fieldType = 'INTEGER(11) UNSIGNED NULL DEFAULT 0';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setName('Actions_ColumnExitPageURL');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param array   $visit
     * @param Action|null $action
     * @return int|bool
     */
    public function onNewVisit(Request $request, $visit, $action)
    {
        $idActionUrl = false;

        if (!empty($action)) {
            $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();
        }

        return (int) $idActionUrl;
    }

    public function onExistingVisit(Request $request, $visit, $action)
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
