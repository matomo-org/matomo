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

class BrowserEngine extends Base
{
    protected $columnName = 'config_browser_engine';
    protected $columnType = 'VARCHAR(10) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('browserEngine');
        $segment->setName('DevicesDetection_BrowserEngine');
        $segment->setAcceptedValues('Trident, WebKit, Presto, Gecko, Blink, etc.');
        $segment->setSuggestedValuesCallback('\DeviceDetector\Parser\Client\Browser\Engine::getAvailableEngines');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_BrowserEngine');
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

        if (!empty($aBrowserInfo['engine'])) {

            return $aBrowserInfo['engine'];
        }

        return '';
    }
}
