<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class OsVersion extends Base
{
    protected $columnName = 'config_os_version';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
    protected $nameSingular = 'DevicesDetection_ColumnOperatingSystemVersion';
    protected $namePlural = 'DevicesDetection_OperatingSystemVersions';
    protected $segmentName = 'operatingSystemVersion';
    protected $acceptValues = 'XP, 7, 2.3, 5.1, ...';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        return $parser->getOs('version');
    }
}
