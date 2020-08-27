<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\UserCountry\Diagnostic\GeolocationDiagnostic;
use Piwik\Url;

class ChallengeConfigureGeolocation extends Challenge
{
    /**
     * @var null|bool
     */
    private $completed = null;

    public function getName()
    {
        return Piwik::translate('Tour_ConfigureGeolocation');
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ConfigureGeolocationDescription');
    }

    public function getId()
    {
        return 'configure_geolocation';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $locationDiagnostic = StaticContainer::get(GeolocationDiagnostic::class);
            $result = $locationDiagnostic->execute();
            $this->completed = !empty($result[0]) && $result[0]->getStatus() === DiagnosticResult::STATUS_OK;
        }
        return $this->completed;
    }

    public function getUrl()
    {
        return 'index.php' . Url::getCurrentQueryStringWithParametersModified(array('module' => 'UserCountry', 'action' => 'adminIndex', 'widget' => false));
    }


}