<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

/**
 * Creates a new segment that can be used for instance within the {@link \Piwik\Columns\Dimension::configureSegment()}
 * method. Make sure to set at least the following values: {@link setName()}, {@link setSegment()},
 * {@link setSqlSegment()}, {@link setType()} and {@link setCategory()}. If you are using a segment in the context of a
 * dimension the type and the SQL segment is usually set for you automatically.
 *
 * Example:
 * ```
 $segment = new \Piwik\Plugin\Segment();
 $segment->setType(\Piwik\Plugin\Segment::TYPE_DIMENSION);
 $segment->setName('General_EntryKeyword');
 $segment->setCategory('General_Visit');
 $segment->setSegment('entryKeyword');
 $segment->setSqlSegment('log_visit.entry_keyword');
 $segment->setAcceptedValues('Any keywords people search for on your website such as "help" or "imprint"');
 ```
 * @api
 * @since 2.5.0
 */
class Segment
{
    /**
     * Segment type 'dimension'. Can be used along with {@link setType()}.
     * @api
     */
    const TYPE_DIMENSION = 'dimension';

    /**
     * Segment type 'metric'. Can be used along with {@link setType()}.
     * @api
     */
    const TYPE_METRIC = 'metric';

    private $type;
    private $category;
    private $name;
    private $segment;
    private $sqlSegment;
    private $sqlFilter;
    private $sqlFilterValue;
    private $acceptValues;
    private $permission;
    private $suggestedValuesCallback;

    /**
     * If true, this segment will only be visible to the user if the user has view access
     * to one of the requested sites (see API.getSegmentsMetadata).
     *
     * @var bool
     */
    private $requiresAtLeastViewAccess = false;

    /**
     * @ignore
     */
    final public function __construct()
    {
        $this->init();
    }

    /**
     * Here you can initialize this segment and set any default values. It is called directly after the object is
     * created.
     * @api
     */
    protected function init()
    {
    }

    /**
     * Here you should explain which values are accepted/useful for your segment, for example:
     * "1, 2, 3, etc." or "comcast.net, proxad.net, etc.". If the value needs any special encoding you should mention
     * this as well. For example "Any URL including protocol. The URL must be URL encoded."
     *
     * @param string $acceptedValues
     * @api
     */
    public function setAcceptedValues($acceptedValues)
    {
        $this->acceptValues = $acceptedValues;
    }

    /**
     * Set (overwrite) the category this segment belongs to. It should be a translation key such as 'General_Actions'
     * or 'General_Visit'.
     * @param string $category
     * @api
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Set (overwrite) the segment display name. This name will be visible in the API and the UI. It should be a
     * translation key such as 'Actions_ColumnEntryPageTitle' or 'Resolution_ColumnResolution'.
     * @param string $name
     * @api
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set (overwrite) the name of the segment. The name should be lower case first and has to be unique. The segment
     * name defined here needs to be set in the URL to actually apply this segment. Eg if the segment is 'searches'
     * you need to set "&segment=searches>0" in the UI.
     * @param string $segment
     * @api
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;
    }

    /**
     * Sometimes you want users to set values that differ from the way they are actually stored. For instance if you
     * want to allow to filter by any URL than you might have to resolve this URL to an action id. Or a country name
     * maybe has to be mapped to a 2 letter country code. You can do this by specifing either a callable such as
     * `array('Classname', 'methodName')` or by passing a closure. There will be four values passed to the given closure
     * or callable: `string $valueToMatch`, `string $segment` (see {@link setSegment()}), `string $matchType`
     * (eg SegmentExpression::MATCH_EQUAL or any other match constant of this class) and `$segmentName`.
     *
     * If the closure returns NULL, then Piwik assumes the segment sub-string will not match any visitor.
     *
     * @param string|\Closure $sqlFilter
     * @api
     */
    public function setSqlFilter($sqlFilter)
    {
        $this->sqlFilter = $sqlFilter;
    }

    /**
     * Similar to {@link setSqlFilter()} you can map a given segment value to another value. For instance you could map
     * "new" to 0, 'returning' to 1 and any other value to '2'. You can either define a callable or a closure. There
     * will be only one value passed to the closure or callable which contains the value a user has set for this
     * segment. This callback is called shortly before {@link setSqlFilter()}.
     * @param string|array $sqlFilterValue
     * @api
     */
    public function setSqlFilterValue($sqlFilterValue)
    {
        $this->sqlFilterValue = $sqlFilterValue;
    }

    /**
     * Defines to which column in the MySQL database the segment belongs: 'mytablename.mycolumnname'. Eg
     * 'log_visit.idsite'. When a segment is applied the given or filtered value will be compared with this column.
     *
     * @param string $sqlSegment
     * @api
     */
    public function setSqlSegment($sqlSegment)
    {
        $this->sqlSegment = $sqlSegment;
    }

    /**
     * @return string
     * @ignore
     */
    public function getSqlSegment()
    {
        return $this->sqlSegment;
    }

    /**
     * Set (overwrite) the type of this segment which is usually either a 'dimension' or a 'metric'.
     * @param string $type See constansts TYPE_*
     * @api
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     * @ignore
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the name of this segment as it should appear in segment expressions.
     *
     * @return string
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Set callback which will be executed when user will call for suggested values for segment.
     *
     * @param callable $suggestedValuesCallback
     */
    public function setSuggestedValuesCallback($suggestedValuesCallback)
    {
        $this->suggestedValuesCallback = $suggestedValuesCallback;
    }

    /**
     * You can restrict the access to this segment by passing a boolean `false`. For instance if you want to make
     * a certain segment only available to users having super user access you could do the following:
     * `$segment->setPermission(Piwik::hasUserSuperUserAccess());`
     * @param bool $permission
     * @api
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return array
     * @ignore
     */
    public function toArray()
    {
        $segment = array(
            'type'       => $this->type,
            'category'   => $this->category,
            'name'       => $this->name,
            'segment'    => $this->segment,
            'sqlSegment' => $this->sqlSegment,
        );

        if (!empty($this->sqlFilter)) {
            $segment['sqlFilter'] = $this->sqlFilter;
        }

        if (!empty($this->sqlFilterValue)) {
            $segment['sqlFilterValue'] = $this->sqlFilterValue;
        }

        if (!empty($this->acceptValues)) {
            $segment['acceptedValues'] = $this->acceptValues;
        }

        if (isset($this->permission)) {
            $segment['permission'] = $this->permission;
        }

        if (is_callable($this->suggestedValuesCallback)) {
            $segment['suggestedValuesCallback'] = $this->suggestedValuesCallback;
        }

        return $segment;
    }

    /**
     * Returns true if this segment should only be visible to the user if the user has view access
     * to one of the requested sites (see API.getSegmentsMetadata), false if it should always be
     * visible to the user (even the anonymous user).
     *
     * @return boolean
     * @ignore
     */
    public function isRequiresAtLeastViewAccess()
    {
        return $this->requiresAtLeastViewAccess;
    }

    /**
     * Sets whether the segment should only be visible if the user requesting it has view access
     * to one of the requested sites and if the user is not the anonymous user.
     *
     * @param boolean $requiresAtLeastViewAccess
     * @ignore
     */
    public function setRequiresAtLeastViewAccess($requiresAtLeastViewAccess)
    {
        $this->requiresAtLeastViewAccess = $requiresAtLeastViewAccess;
    }
}
