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
use Piwik\Url;

class ChallengeTrackingCode extends Challenge
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
        return Piwik::translate('Tour_EmbedTrackingCode');
    }

    public function getDescription()
    {
        return Piwik::translate('CoreAdminHome_TrackingCodeIntro');
    }

    public function getId()
    {
        return 'track_data';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $this->completed = $this->finder->hasTrackedData();
        }
        return $this->completed;
    }

    public function getUrl()
    {
        return 'index.php' . Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreAdminHome', 'action' => 'trackingCodeGenerator', 'widget' => false));
    }


}