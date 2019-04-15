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

class ChallengeAddedWebsite extends Challenge
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
        return Piwik::translate('Tour_AddWebsite');
    }

    public function getDescription()
    {
        return Piwik::translate('SitesManager_PluginDescription');
    }

    public function getId()
    {
        return 'add_website';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $this->completed = $this->finder->hasAddedWebsite(Piwik::getCurrentUserLogin());
        }
        return $this->completed;
    }

    public function getUrl()
    {
        return 'index.php' . Url::getCurrentQueryStringWithParametersModified(array('module' => 'SitesManager', 'action' => 'index', 'widget' => false));
    }


}