<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

/**
 * A consumer is a user having specified a license key in the Marketplace.
 */
class Consumer
{
    /**
     * @var Api\Client
     */
    private $marketplaceClient;

    private $consumer = false;
    private $isValid = null;

    /**
     * @var array
     */
    private $pluginLicenseStatus = null;

    public function __construct(Api\Client $marketplaceClient)
    {
        $this->marketplaceClient = $marketplaceClient;
    }

    /**
     * For tests only.
     * @internal
     * @return Api\Client
     */
    public function getApiClient()
    {
        return $this->marketplaceClient;
    }

    public function clearCache()
    {
        $this->consumer = false;
        $this->isValid = null;
    }

    public function getConsumer()
    {
        if ($this->consumer === false) {
            $consumer = $this->marketplaceClient->getConsumer();
            if (!empty($consumer)) {
                $this->consumer = $consumer;
            } else {
                $this->consumer = array();
            }
        }

        return $this->consumer;
    }

    public function isValidConsumer()
    {
        if (!isset($this->isValid)) {
            $this->isValid = $this->marketplaceClient->isValidConsumer();
        }

        return $this->isValid;
    }

    public function getConsumerPluginLicenseStatus(): array
    {
        if ($this->pluginLicenseStatus === null) {
            $consumer = $this->getConsumer();
            $this->pluginLicenseStatus = [];
            if (!empty($consumer['licenses'])) {
                foreach ($consumer['licenses'] as $license) {
                    $this->pluginLicenseStatus[$license['plugin']['name']] = $license['status'];
                }
            }
        }

        return $this->pluginLicenseStatus;
    }
}
