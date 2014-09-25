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
 * This example dimension counts achievement points for each user. A user gets one achievement point for each action
 * plus five extra achievement points for each conversion. This would allow you to create a ranking showing the most
 * active/valueable users. It is just an example, you can log pretty much everything and even just store any custom
 * request url property. Please note that dimension instances are usually cached during one tracking request so they
 * should be stateless (meaning an instance of this dimension will be reused if requested multiple times).
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin\Dimension\VisitDimension} for more information.
 */
class Random extends VisitDimension
{
    /**
     * This will be the name of the column in the log_visit table if a $columnType is specified.
     * @var string
     */
    protected $columnName = 'random';

    /**
     * @var string
     */
    protected $columnType = "VARCHAR(10) NOT NULL DEFAULT ''";

    /**
     * The onNewVisit method is triggered when a new visitor is detected. This means here you can define an initial
     * value for this user. By returning boolean false no value will be saved. Once the user makes another action the
     * event "onExistingVisit" is executed. That means for each visitor this method is executed once. If you do not want
     * to perform any action on a new visit you can just remove this method.
     *
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
     * The onExistingVisit method is triggered when a visitor was recognized meaning it is not a new visitor.
     * If you want you can overwrite any previous value set by the event onNewVisit. By returning boolean false no value
     * will be updated. If you do not want to perform any action on a new visit you can just remove this method.
     *
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