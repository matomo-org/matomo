<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\UserSettings\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class Resolution extends VisitDimension
{    
    protected $fieldName = 'config_resolution';
    protected $fieldType = 'VARCHAR(9) NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('resolution');
        $segment->setName('UserSettings_ColumnResolution');
        $segment->setAcceptValues('1280x1024, 800x600, etc.');
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