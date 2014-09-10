<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Common;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;

class LocationFetcherProvider
{
    /**
     * @var string
     */
    protected $currentLocationProviderId;

    /**
     * @var callable
     */
    protected $getProviderByIdCallback;

    /**
     * @var string
     */
    protected $defaultProviderId;

    /**
     * @param null|string $currentLocationProviderId
     * @param null|callable $getProviderByIdCallback
     * @param null|string $defaultProviderId
     */
    public function __construct($currentLocationProviderId = null, $getProviderByIdCallback = null, $defaultProviderId = null)
    {
        if ($currentLocationProviderId === null) {
            $currentLocationProviderId = Common::getCurrentLocationProviderId();
        }

        $this->currentLocationProviderId = $currentLocationProviderId;

        if (!is_callable($getProviderByIdCallback)) {
            $getProviderByIdCallback = array('LocationProvider', 'getProviderById');
        }

        $this->getProviderByIdCallback = $getProviderByIdCallback;

        if ($defaultProviderId === null) {
            $defaultProviderId = DefaultProvider::ID;
        }

        $this->defaultProviderId = $defaultProviderId;
    }

    /**
     * @return false|LocationProvider
     */
    public function get()
    {
        $id = $this->currentLocationProviderId;
        $provider = call_user_func($this->getProviderByIdCallback, $id);

        if ($provider === false) {
            $provider = $this->getDefaultProvider();
            Common::printDebug("GEO: no current location provider sent, falling back to default '$id' one.");
        }

        return $provider;
    }

    /**
     * @return false|LocationProvider
     */
    public function getDefaultProvider()
    {
        return call_user_func($this->getProviderByIdCallback, $this->defaultProviderId);
    }

    /**
     * @param false|LocationProvider $provider
     * @return bool
     */
    public function isDefaultProvider($provider)
    {
        return !empty($provider) && $this->defaultProviderId == $provider->getId();
    }
} 
