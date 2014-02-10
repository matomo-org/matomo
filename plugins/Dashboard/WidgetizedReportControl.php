<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\View;
use Piwik\WidgetsList;
use Piwik\FrontController;

/**
 * Renders a dashboard widget that surrounds a report. The view can be rendered
 * as a template as well as with a report.
 *
 * The dashboard creates widgets through JavaScript, but this control can be used
 * to render widgets server-side.
 */
class WidgetizedReportControl extends View
{
    const TEMPLATE = "@Dashboard/_widgetizedReport";

    /**
     * Constructor.
     */
    public function __construct($apiModule = false, $apiAction = false, $parameterOverride = array())
    {
        parent::__construct(self::TEMPLATE);

        $this->apiModule = $apiModule;
        $this->apiAction = $apiAction;
        $this->parameterOverride = array();

        $this->renderEmpty = false;
        $this->self = $this;

        $widgetInfo = $this->getWidgetInfo();
        if ($widgetInfo) {
            $this->uniqueWidgetId = $widgetInfo['uniqueId'];
            $this->widgetName = $widgetInfo['name'];
        }
    }

    public function getReport() // TODO: should ideally create a ViewDataTable instance
    {
        $savedGET = array('module' => $this->apiModule, 'action' => $this->apiAction)
                  + $this->parameterOverride
                  + $_GET;

        $result = FrontController::getInstance()->dispatch();

        $_GET = $savedGET;

        return $result;
    }

    private function getWidgetInfo()
    {
        if ($this->renderEmpty
            || $this->apiModule === false
            || $this->apiAction === false
        ) {
            return false;
        }

        foreach (WidgetsList::get() as $widgetInfo) {
            if ($widgetInfo['parameters']['module'] == $this->apiModule
                && $widgetInfo['parameters']['action'] == $this->apiAction
            ) {
                return $widgetInfo;
            }
        }

        return false;
    }
}