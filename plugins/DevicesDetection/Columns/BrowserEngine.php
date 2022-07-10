<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserEngine extends Base
{
    protected $columnName = 'config_browser_engine';
    protected $columnType = 'VARCHAR(10) NULL';
    protected $segmentName = 'browserEngine';
    protected $nameSingular = 'DevicesDetection_BrowserEngine';
    protected $namePlural = 'DevicesDetection_BrowserEngines';
    protected $acceptValues = 'Trident, WebKit, Presto, Gecko, Blink, etc.';
    protected $suggestedValuesCallback = '\DeviceDetector\Parser\Client\Browser\Engine::getAvailableEngines';
    protected $type = self::TYPE_TEXT;

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getBrowserEngineName($value);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        $aBrowserInfo = $parser->getClient();

        if (!empty($aBrowserInfo['engine'])) {

            return $aBrowserInfo['engine'];
        }

        return '';
    }
}
