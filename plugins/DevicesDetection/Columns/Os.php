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
use Piwik\Tracker\Settings;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class Os extends Base
{
    protected $columnName = 'config_os';
    protected $columnType = 'CHAR(3) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('operatingSystemCode');
        $segment->setName('DevicesDetection_ColumnOperatingSystem');
        $segment->setAcceptedValues('WIN, MAC, LIN, AND, IPD, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_OperatingSystemFamily');
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

        if ($parser->isBot()) {
            $os = Settings::OS_BOT;
        } else {
            $os = $parser->getOS();
            $os = empty($os['short_name']) ? 'UNK' : $os['short_name'];
        }

        return $os;
    }
}
