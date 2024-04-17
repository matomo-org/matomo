<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetPlanetRatiosWithLogos extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Advanced tag cloud: with logos and links');
        $this->documentation = 'This report shows a sample tab cloud.';
        $this->subcategoryId = 'Tag clouds';
        $this->order = 113;
    }

    public function getDefaultTypeViewDataTable()
    {
        return Cloud::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->display_logo_instead_of_label = true;
        $view->config->columns_to_display = array('label', 'value');
        $view->config->addTranslation('value', 'times the diameter of Earth');
    }
}
