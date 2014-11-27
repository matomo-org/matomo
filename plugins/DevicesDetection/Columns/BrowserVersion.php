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

class BrowserVersion extends Base
{
    protected $columnName = 'config_browser_version';
    protected $columnType = 'VARCHAR(20) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('browserVersion');
        $segment->setName('DevicesDetection_BrowserVersion');
        $segment->setAcceptedValues('1.0, 8.0, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_BrowserVersion');
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

        $aBrowserInfo = $parser->getClient();

        if (!empty($aBrowserInfo['version'])) {

            return $aBrowserInfo['version'];
        }

        return '';
    }
}
