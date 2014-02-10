<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\View;

/**
 * TODO
 */
class WidgetizedReportControl extends View
{
    const TEMPLATE = "@Dashboard/_widgetizedReport";

    /**
     * TODO
     */
    public function __construct($apiModule = false, $apiAction = false, $parameterOverride = array(),
                                $renderEmpty = false)
    {
        parent::__construct(self::TEMPLATE);

        $this->reportData = '';
        if (!$renderEmpty) {
            $this->reportData = // TODO
        }

        $this->uniqueWidgetId = // TODO
        $this->widgetName = // TODO
    }
}