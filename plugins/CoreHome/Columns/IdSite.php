<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class IdSite extends VisitDimension
{
    protected $columnName = 'idsite';
    // we do not install or define column definition here as we need to create this column when installing as there is
    // an index on it. Currently we do not define the index here... although we could overwrite the install() method
    // and add column 'idsite' and add index. Problem is there is also an index
    // INDEX(idsite, config_id, visit_last_action_time) and we maybe not be sure whether config_id already exists at
    // installing point (we do not know whether visit_last_action_time or idsite column would be added first).

    protected $nameSingular = 'General_Measurable';
    protected $namePlural = 'General_Measurables';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->getIdSite();
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $request->getIdSite();
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        try {
            return Site::getNameFor($value);
        } catch (\Exception $ex) {
            $formatted = parent::formatValue($value, $idSite, $formatter);
            return Piwik::translate('General_MeasurableId') . ': ' . $formatted;
        }
    }
}
