<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

/**
 * Generates a random string each time a visitor will be updated to be able to differentiate between visitor not found
 * and visitor found but no value changed. See https://github.com/piwik/piwik/issues/6296 and
 * https://github.com/piwik/piwik/pull/6298
 */
class Random extends VisitDimension
{
    /**
     * @var string
     */
    protected $columnName = 'random';

    /**
     * @var string
     */
    protected $columnType = "VARCHAR(10) NOT NULL DEFAULT ''";

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->generateRandomString();
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     *
     * @return mixed|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->generateRandomString();
    }

    private function generateRandomString()
    {
        $rand   = mt_rand(0, 5000);
        $unique = uniqid($rand, true);
        $hash   = md5($unique);

        return substr($hash, 0, 10);
    }

}