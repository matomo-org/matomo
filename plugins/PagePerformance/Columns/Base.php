<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Columns;

use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

abstract class Base extends ActionDimension
{
    protected $type = self::TYPE_DURATION_MS;

    abstract public function getRequestParam();

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        if (!($action instanceof ActionPageview)) {
            return false;
        }

        $time = $request->getParam($this->getRequestParam());

        if ($time === -1) {
            return false;
        }

        if ($time < 0) {
            throw new InvalidRequestParameterException(sprintf('Value for %1$s can\'t be negative.', $this->getRequestParam()));
        }

        // ignore obviously incorrect values (nothing should take more than 1 hour to load)
        // refs https://github.com/matomo-org/matomo/issues/17035
        if ($time > 3600000) {
            return false;
        }

        return $time;
    }
}
