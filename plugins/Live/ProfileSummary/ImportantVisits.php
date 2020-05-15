<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Live\ProfileSummary;

use Piwik\Piwik;
use Piwik\View;

/**
 * Class ImportantVisits
 */
class ImportantVisits extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('General_Summary');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $viewVisits              = new View('@Live/_profileSummaryVisits.twig');
        $viewVisits->visitorData = $this->profile;
        return $viewVisits->render();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 30;
    }
}