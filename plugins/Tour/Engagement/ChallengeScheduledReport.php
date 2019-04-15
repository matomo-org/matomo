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

class ChallengeScheduledReport extends Challenge
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
        return Piwik::translate('Tour_AddReport');
    }

    public function getId()
    {
        return 'add_report';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $this->completed = $this->finder->hasAddedNewEmailReport(Piwik::getCurrentUserLogin());
        }
        return $this->completed;
    }

    public function getInAppLink()
    {
        return  array('module' => 'ScheduledReports', 'action' => 'index', 'widget' => false);
    }


}