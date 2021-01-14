<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins\Columns;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class PluginQuickTime extends DevicePluginColumn
{
    protected $columnName = 'config_quicktime';
    protected $columnType = 'TINYINT(1) NULL';
    protected $type = self::TYPE_BOOL;

    public function getName()
    {
        return Piwik::translate('General_Plugin') . ' (QuickTime)';
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return Common::getRequestVar('qt', 0, 'int', $request->getParams());
    }
}