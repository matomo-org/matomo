<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Resolution extends VisitDimension
{
    protected $columnName = 'config_resolution';
    protected $columnType = 'VARCHAR(18) NULL';
    protected $acceptValues = '1280x1024, 800x600, etc.';
    protected $segmentName = 'resolution';
    protected $nameSingular = 'Resolution_ColumnResolution';
    protected $namePlural = 'Resolution_Resolutions';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $resolution = $request->getParam('res');

        if (!empty($resolution)) {
            return substr($resolution, 0, 9);
        }

        return $resolution;
    }

}