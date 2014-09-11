<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleTracker\Columns;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;

/**
 * This example dimension counts achievement points for each user. A user gets one achievement point for each action
 * plus five extra achievement points for each conversion. This would allow you to create a ranking showing the most
 * active/valueable users. It is just an example, you can log pretty much everything and even just store any custom
 * request url property. Please note that dimension instances are usually cached during one tracking request so they
 * should be stateless (meaning an instance of this dimension will be reused if requested multiple times).
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin\Dimension\ConversionDimension} for more information.
 */
class ExampleConversionDimension extends ConversionDimension
{
    /**
     * This will be the name of the column in the log_conversion table if a $columnType is specified.
     * @var string
     */
    protected $columnName = 'example_conversion_dimension';

    /**
     * If a columnType is defined, we will create this a column in the MySQL table having this type. Please make sure
     * MySQL will understand this type. Once you change the column type the Piwik platform will notify the user to
     * perform an update which can sometimes take a long time so be careful when choosing the correct column type.
     * @var string
     */
    protected $columnType = 'INTEGER(11) DEFAULT 0 NOT NULL';

    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    public function getName()
    {
        return Piwik::translate('ExampleTracker_DimensionName');
    }

    /**
     * By defining one or multiple segments a user will be able to filter their visitors by this column. For instance
     * show all reports only considering users having more than 10 achievement points. If you do not want to define a
     * segment for this dimension just remove the column.
     */
    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('myConversionSegmentName');
        $segment->setCategory('General_Visit');
        $segment->setName('ExampleTracker_DimensionName');
        $segment->setAcceptedValues('Here you should explain which values are accepted/useful: Any number, for instance 1, 2, 3 , 99');
        $this->addSegment($segment);
    }

    /**
     * This event is triggered when an ecommerce order is converted. In this example we would store a "0" in case it
     * was the visitors first action or "1" otherwise.
     * Return boolean false if you do not want to change the value in some cases. If you do not want to perform any
     * action on an ecommerce order at all it is recommended to just remove this method.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onEcommerceOrderConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        if ($visitor->isVisitorKnown()) {
            return 1;
        }

        return 0;
    }

    /**
     * This event is triggered when an ecommerce cart update is converted. In this example we would store a
     * the value of the tracking url parameter "myCustomParam" in the "example_conversion_dimension" column.
     * Return boolean false if you do not want to change the value in some cases. If you do not want to perform any
     * action on an ecommerce order at all it is recommended to just remove this method.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onEcommerceCartUpdateConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return Common::getRequestVar('myCustomParam', $default = false, 'int', $request->getParams());
    }

    /**
     * This event is triggered when an any custom goal is converted. In this example we would store a the id of the
     * goal in the 'example_conversion_dimension' column if the visitor is known and nothing otherwise.
     * Return boolean false if you do not want to change the value in some cases. If you do not want to perform any
     * action on an ecommerce order at all it is recommended to just remove this method.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onGoalConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        $goalId = $goalManager->getGoalColumn('idgoal');

        if ($visitor->isVisitorKnown()) {
            return $goalId;
        }

        return false;
    }

}