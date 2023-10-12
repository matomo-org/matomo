<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public function advertisingHeatmaps()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');
        $this->setGeneralVariablesView($view);

        $view->title = 'Unlock the power of Heatmaps';
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-heatmaps.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "Get visual representations of user interactions on your website, making it easy to understand how visitors engage with your content. Get visual representations of user interactions on your website, making it easy to understand how visitors engage with your content.",
            "Get actionable data to optimise your website's layout, content placement, and user experience.",
            "Identify and address user behaviour patterns, to increase conversion rates and achieve better results from your digital efforts.",
        ];
        $view->legalInfo = 'This is some legal information worth knowing about. Can have a link to <a href="#">learn more</a>.';

        return $view->render();
    }

}
