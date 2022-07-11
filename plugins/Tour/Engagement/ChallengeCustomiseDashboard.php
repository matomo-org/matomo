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
use Piwik\Plugins\Tour\Dao\DataFinder;

class ChallengeCustomiseDashboard extends Challenge
{
    /**
     * @var DataFinder
     */
    private $finder;

    /**
     * @var null|bool
     */
    private $completed = null;

    public function __construct(DataFinder $dataFinder)
    {
        $this->finder = $dataFinder;
    }

    public function getName()
    {
        return Piwik::translate('Tour_CustomiseDashboard');
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_CustomiseDashboardDescription');
    }

    public function getId()
    {
        return 'customise_dashboard';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $login = Piwik::getCurrentUserLogin();
            $this->completed = $this->finder->hasAddedOrCustomisedDashboard($login);
        }
        return $this->completed;
    }

    public function getUrl()
    {
        return 'https://matomo.org/faq/dashboards/create-dashboards-and-customise-widgets-and-layout/';
    }


}