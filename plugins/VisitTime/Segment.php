<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

/**
 * VisitTime segment base class.
 *
 */
class Segment extends \Piwik\Plugin\Segment
{
    protected  function init()
    {
        $this->setCategory('Visit');
    }
}
