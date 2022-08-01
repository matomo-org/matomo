<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;

class ChallengeChangeVisualisation extends Challenge
{
    public function getName()
    {
        return Piwik::translate('Tour_ChangeVisualisation');
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ChangeVisualisationDescription');
    }

    public function getId()
    {
        return 'change_visualisations';
    }

    public function getUrl()
    {
        return 'https://matomo.org/faq/reports/graphs-and-visualisations-in-matomo/';
    }


}