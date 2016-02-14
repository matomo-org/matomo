<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Segment;
use Piwik\Tracker\Action;
use Exception;

/**
 * This example dimension only defines a name and does not track any data. It's supposed to be only used in reports.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Columns\Dimension} for more information.
 */
class ActionType extends ActionDimension
{
    private $types = array(
        Action::TYPE_PAGE_URL => 'pageviews',
        Action::TYPE_CONTENT => 'contents',
        Action::TYPE_SITE_SEARCH => 'sitesearches',
        Action::TYPE_EVENT => 'events',
        Action::TYPE_OUTLINK => 'outlinks',
        Action::TYPE_DOWNLOAD => 'downloads'
    );

    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    public function getName()
    {
        return Piwik::translate('Actions_ActionType');
    }

    protected function configureSegments()
    {
        $types = $this->types;

        $segment = new Segment();
        $segment->setSegment('actionType');
        $segment->setName('Actions_ActionType');
        $segment->setSqlSegment('log_action.type');
        $segment->setType(Segment::TYPE_DIMENSION);
        $segment->setAcceptedValues(sprintf('A type of action, such as: %s', implode(', ', $types)));
        $segment->setSqlFilter(function ($type) use ($types) {
            if (array_key_exists($type, $types)) {
                return $type;
            }

            $index = array_search(strtolower(trim(urldecode($type))), $types);

            if ($index === false) {
                throw new Exception("actionType must be one of: " . implode(', ', $types));
            }

            return $index;
        });
        $segment->setSuggestedValuesCallback(function ($idSite, $maxSuggestionsToReturn) use ($types) {
            return array_slice(array_values($types), 0, $maxSuggestionsToReturn);
        });
        $this->addSegment($segment);
    }
}