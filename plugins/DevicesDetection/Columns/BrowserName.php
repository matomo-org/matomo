<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use DeviceDetector\Parser\Client\Browser;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserName extends Base
{
    protected $columnName = 'config_browser_name';
    protected $columnType = 'VARCHAR(10) NULL';
    protected $segmentName = 'browserName';
    protected $nameSingular = 'DevicesDetection_ColumnBrowser';
    protected $namePlural = 'DevicesDetection_Browsers';
    protected $acceptValues = 'FireFox, Internet Explorer, Chrome, Safari, Opera etc.';
    protected $type = self::TYPE_TEXT;

    public function __construct()
    {
        $this->sqlFilterValue = function ($val) {
            $browsers = Browser::getAvailableBrowsers();
            $result   = array_search($val, $browsers);

            if ($result === false) {
                $result = 'UNK';
            }

            return $result;
        };
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return array_values(Browser::getAvailableBrowsers() + ['Unknown']);
        };
    }

    protected function configureSegments()
    {
        parent::configureSegments();

        $segment = new Segment();
        $segment->setSegment('browserCode');
        $segment->setName('DevicesDetection_BrowserCode');
        $segment->setAcceptedValues('FF, IE, CH, SF, OP etc.');
        $this->suggestedValuesCallback = null;
        $this->sqlFilterValue = null;
        $this->addSegment($segment);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getBrowserName($value);
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

        if (!empty($aBrowserInfo['short_name'])) {

            return $aBrowserInfo['short_name'];
        }

        return 'UNK';
    }
}
