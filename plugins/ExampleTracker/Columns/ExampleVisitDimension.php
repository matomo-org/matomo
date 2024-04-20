<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleTracker\Columns;

use Piwik\Common;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

/**
 * This example dimension counts achievement points for each user. A user gets one achievement point for each action
 * plus five extra achievement points for each conversion. This would allow you to create a ranking showing the most
 * active/valuable users. It is just an example, you can log pretty much everything and even just store any custom
 * request url property. Please note that dimension instances are usually cached during one tracking request so they
 * should be stateless (meaning an instance of this dimension will be reused if requested multiple times).
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin\Dimension\VisitDimension} for more information.
 */
class ExampleVisitDimension extends VisitDimension
{
    /**
     * This will be the name of the column in the log_visit table if a $columnType is specified.
     * @var string
     */
    protected $columnName = 'example_visit_dimension';

    /**
     * If a columnType is defined, we will create this a column in the MySQL table having this type. Please make sure
     * MySQL will understand this type. Once you change the column type the Piwik platform will notify the user to
     * perform an update which can sometimes take a long time so be careful when choosing the correct column type.
     * @var string
     */
    protected $columnType = 'INTEGER(11) DEFAULT 0 NULL';

    /**
     * The type of the dimension is automatically detected by the columnType. If the type of the dimension is not
     * detected correctly, you may want to adjust the type manually. The configured type will affect how the dimension
     * is formatted in the UI.
     * @var string
     */
    // protected $type = self::TYPE_NUMBER;

    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    protected $nameSingular = 'ExampleTracker_DimensionName';

    /**
     * By defining a segment a user will be able to filter their visitors by this column. For instance
     * show all reports only considering users having more than 10 achievement points. If you do not want to define a
     * segment for this dimension, simply leave the name empty.
     */
    protected $segmentName = 'achievementPoints';

    protected $acceptValues = 'Here you should explain which values are accepted/useful for segments: Any number, for instance 1, 2, 3 , 99';

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
        $paramValue = Common::getRequestVar('myCustomVisitParam', '', 'string', $request->getParams());
        if (!empty($paramValue)) {
            return $paramValue;
        }

        if (empty($action)) {
            return 0;
        }

        return 1;

        // you could also easily save any custom tracking url parameters
        // return Common::getRequestVar('myCustomTrackingParam', 'default', 'string', $request->getParams());
        // return Common::getRequestVar('linuxversion', false, 'string', $request->getParams());
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
        $paramValue = Common::getRequestVar('myCustomVisitParam', '', 'string', $request->getParams());
        if (!empty($paramValue)) {
            return $paramValue;
        }

        if (empty($action)) {
            return false; // Do not change an already persisted value
        }

        return $visitor->getVisitorColumn($this->columnName) + 1;
    }

    /**
     * This event is executed shortly after "onNewVisit" or "onExistingVisit" in case the visitor converted a goal.
     * In this example we give the user 5 extra points for this achievement. Usually this event is not needed and you
     * can simply remove this method therefore. An example would be for instance to persist the last converted
     * action url. Return boolean false if you do not want to change the current value.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     *
     * @return mixed|false
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName) + 5;  // give this visitor 5 extra achievement points
    }

    /**
     * By implementing this event you can persist a value to the log_conversion table in case a conversion happens.
     * The persisted value will be logged along the conversion and will not be changed afterwards.
     * This allows you to generate reports that shows for instance which url was called how often for a specific
     * conversion. Once you implement this event and a $columnType is defined a column in the log_conversion MySQL table
     * will be created automatically.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     *
     * @return mixed
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }
     */

    /**
     * Sometimes you may want to make sure another dimension is executed before your dimension so you can persist
     * a value depending on the value of other dimensions. You can do this by defining an array of dimension names.
     * If you access any value of any other column within your events, you should require them here. Otherwise those
     * values may not be available.
     * @return array
    public function getRequiredVisitFields()
    {
        return array('idsite', 'server_time');
    }
    */
}
