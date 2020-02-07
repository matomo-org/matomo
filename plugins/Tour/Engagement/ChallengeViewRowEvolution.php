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

class ChallengeViewRowEvolution extends Challenge
{
    public function getName()
    {
        return Piwik::translate('Tour_ViewX', Piwik::translate('Tour_RowEvolution'));
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ViewRowEvolutionDescription');
    }

    public function getId()
    {
        return 'view_row_evolution';
    }

    public function getUrl()
    {
        return 'https://matomo.org/docs/row-evolution/';
    }


}