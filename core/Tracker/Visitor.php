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
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

class Visitor
{
    private $visitorKnown = false;
    public $visitProperties;

    public function __construct(VisitProperties $visitProperties, $isVisitorKnown = false)
    {
        $this->visitProperties = $visitProperties;
        $this->setIsVisitorKnown($isVisitorKnown);
    }

    public static function makeFromVisitProperties(VisitProperties $visitProperties, Request $request)
    {
        $isKnown = $request->getMetadata('CoreHome', 'isVisitorKnown');
        return new Visitor($visitProperties, $isKnown);
    }

    public function setVisitorColumn($column, $value)
    {
        $this->visitProperties->setProperty($column, $value);
    }

    public function getVisitorColumn($column)
    {
        if (array_key_exists($column, $this->visitProperties->getProperties())) {
            return $this->visitProperties->getProperty($column);
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