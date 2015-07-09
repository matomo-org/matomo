<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

class Visitor
{
    private $visitorKnown = false;
    private $request;
    private $visitProperties;
    private $configId;

    public function __construct(Request $request, $configId, VisitProperties $visitProperties, $isVisitorKnown = false)
    {
        $this->request = $request;
        $this->configId = $configId;
        $this->visitProperties = $visitProperties;
        $this->setIsVisitorKnown($isVisitorKnown);
    }

    public function setVisitorColumn($column, $value) // TODO: remove this eventually
    {
        $this->visitProperties->visitorInfo[$column] = $value;
    }

    public function getVisitorColumn($column)
    {
        if (array_key_exists($column, $this->visitProperties->visitorInfo)) {
            return $this->visitProperties->visitorInfo[$column];
        }

        return false;
    }

    public function isVisitorKnown()
    {
        return $this->visitorKnown === true;
    }

    private function setIsVisitorKnown($isVisitorKnown)
    {
        return $this->visitorKnown = $isVisitorKnown;
    }
}
