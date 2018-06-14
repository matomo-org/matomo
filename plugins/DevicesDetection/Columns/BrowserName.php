<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserName extends Base
{
    protected $columnName = 'config_browser_name';
    protected $columnType = 'VARCHAR(10) NULL';
    protected $segmentName = 'browserCode';
    protected $nameSingular = 'DevicesDetection_ColumnBrowser';
    protected $namePlural = 'DevicesDetection_Browsers';
    protected $acceptValues = 'FF, IE, CH, SF, OP, etc.';
    protected $type = self::TYPE_TEXT;

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
