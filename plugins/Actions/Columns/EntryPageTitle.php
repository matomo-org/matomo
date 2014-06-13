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

class EntryPageTitle extends VisitDimension
{
    protected $fieldName = 'visit_entry_idaction_name';
    protected $fieldType = 'INTEGER(11) UNSIGNED NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('entryPageTitle');
        $segment->setName('Actions_ColumnEntryPageTitle');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param array   $visit
     * @param Action|null $action
     * @return int
     */
    public function onNewVisit(Request $request, $visit, $action)
    {
        $idActionName = false;

        if (!empty($action)) {
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
        }

        return (int) $idActionName;
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnEntryPageTitle');
    }
}
