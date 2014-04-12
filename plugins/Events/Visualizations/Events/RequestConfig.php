<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Events\Visualizations\Events;

use Piwik\Plugins\Events\Visualizations\Events;

class RequestConfig extends \Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable\RequestConfig
{
    public $secondaryDimension = '';

    public function __construct()
    {
        parent::__construct();
        $properties = array(
            'secondaryDimension',
        );
        $this->addPropertiesThatShouldBeAvailableClientSide($properties);
        $this->addPropertiesThatCanBeOverwrittenByQueryParams($properties);
    }
}

