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
    public const CONSUMER_LICENSE_STATUS_ACTIVE = 'Active';

    /**
     * @var Api\Client
     */
    private $marketplaceClient;

    private $consumer = false;
    private $isValid = null;

    /**
     * @var array
     */
    private $consumerLicenseStatusPluginWise = null;

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

    public function getConsumerLicenseStatusPluginWise(): array
    {
        if (!$this->consumerLicenseStatusPluginWise) {
            $consumer = $this->getConsumer();
            if (!empty($consumer['licenses'])) {
                foreach ($consumer['licenses'] as $license) {
                    $this->consumerLicenseStatusPluginWise[$license['plugin']['name']] = ['licenseStatus' => $license['status']];
                }
            }
        }

        return $this->consumerLicenseStatusPluginWise;
    }
}
