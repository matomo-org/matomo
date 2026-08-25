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
     * Whether the Marketplace actually answered the last consumer request, as opposed to the
     * request failing. An answer listing no license for a plugin is a real answer.
     * @var bool
     */
    private $consumerAvailable = false;

    /**
     * @var array|null
     */
    private $pluginLicenseStatus = null;

    /**
     * @var array|null
     */
    private $pluginLicenses = null;

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
        $this->consumerAvailable = false;
        $this->pluginLicenseStatus = null;
        $this->pluginLicenses = null;
    }

    public function getConsumer()
    {
        if ($this->consumer === false) {
            $consumer = $this->marketplaceClient->getConsumer();
            $this->consumerAvailable = $consumer !== null;

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
            $this->pluginLicenseStatus = [];
            foreach ($this->getConsumerPluginLicenses() ?: [] as $pluginName => $license) {
                $this->pluginLicenseStatus[$pluginName] = $license['status'];
            }
        }

        return $this->pluginLicenseStatus;
    }

    /**
     * Returns the consumer's license for each plugin it covers, keyed by plugin name.
     *
     * A plugin returned by the Marketplace carries a copy of the consumer's license for it, but the
     * plugin lists are cached for longer than the consumer response, so that copy can describe a
     * license the consumer no longer has or has only just bought. This is the current one.
     *
     * Returns null, rather than an empty list, when the Marketplace could not be reached: the caller
     * then has nothing current to go on and keeps using the copy the plugin carries.
     *
     * @return array<string, array<string, mixed>>|null
     */
    public function getConsumerPluginLicenses(): ?array
    {
        if ($this->pluginLicenses === null) {
            $consumer = $this->getConsumer();

            if (!$this->consumerAvailable) {
                return null;
            }

            $this->pluginLicenses = [];

            if (!empty($consumer['licenses'])) {
                foreach ($consumer['licenses'] as $license) {
                    if (!empty($license['plugin']['name'])) {
                        $this->pluginLicenses[$license['plugin']['name']] = $license;
                    }
                }
            }
        }

        return $this->pluginLicenses;
    }
}
