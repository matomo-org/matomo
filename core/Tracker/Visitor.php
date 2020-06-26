<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Tracker\Visit\VisitProperties;

class Visitor
{
    private $visitorKnown = false;

    /**
     * @var VisitProperties
     */
    public $visitProperties;

    /**
     * @var VisitProperties
     */
    public $previousVisitProperties;

    public function __construct(VisitProperties $visitProperties, $isVisitorKnown = false, VisitProperties $previousVisitProperties = null)
    {
        $this->visitProperties = $visitProperties;
        $this->previousVisitProperties = $previousVisitProperties;
        $this->setIsVisitorKnown($isVisitorKnown);
    }

    public static function makeFromVisitProperties(VisitProperties $visitProperties, Request $request, VisitProperties $previousVisitProperties = null)
    {
        $isKnown = $request->getMetadata('CoreHome', 'isVisitorKnown');
        return new Visitor($visitProperties, $isKnown, $previousVisitProperties);
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

    public function getPreviousVisitColumn($column)
    {
        if (empty($this->previousVisitProperties)) {
            return false;
        }

        if (array_key_exists($column, $this->previousVisitProperties->getProperties())) {
            return $this->previousVisitProperties->getProperty($column);
        }

        return false;
    }

    public function isVisitorKnown()
    {
        return $this->visitorKnown === true;
    }

    public function isNewVisit()
    {
        return !$this->isVisitorKnown();
    }

    private function setIsVisitorKnown($isVisitorKnown)
    {
        return $this->visitorKnown = $isVisitorKnown;
    }
}