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
use Piwik\Plugins\UserSettings\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Resolution extends VisitDimension
{
    protected $columnName = 'config_resolution';
    protected $columnType = 'VARCHAR(9) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('resolution');
        $segment->setName('UserSettings_ColumnResolution');
        $segment->setAcceptedValues('1280x1024, 800x600, etc.');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $resolution = $request->getParam('res');

        if (!empty($resolution)) {
            return substr($resolution, 0, 9);
        }

        return $resolution;
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnResolution');
    }
}