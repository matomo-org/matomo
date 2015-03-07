<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class OsVersion extends Base
{
    protected $columnName = 'config_os_version';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('operatingSystemVersion');
        $segment->setName('DevicesDetection_ColumnOperatingSystemVersion');
        $segment->setAcceptedValues('XP, 7, 2.3, 5.1, ...');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_OperatingSystemVersions');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getOs('version');
    }
}
