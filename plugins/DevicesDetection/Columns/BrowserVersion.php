<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserVersion extends Base
{
    protected $columnName = 'config_browser_version';
    protected $columnType = 'VARCHAR(20) NULL';
    protected $segmentName = 'browserVersion';
    protected $nameSingular = 'DevicesDetection_BrowserVersion';
    protected $namePlural = 'DevicesDetection_BrowserVersions';
    protected $acceptValues = '1.0, 8.0, etc.';
    protected $type = self::TYPE_TEXT;

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
