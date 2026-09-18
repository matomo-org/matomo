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
    private Api\Client $marketplaceClient;

    private $consumer = false;
    private $isValid = null;

    /**
     * Whether the Marketplace actually answered the last consumer request, as opposed to the
     * request failing or arriving empty. An answer whose license list is empty is a real answer.
     */
    private bool $consumerAvailable = false;

    private ?array $pluginLicenseStatus = null;

    /** @var array<int, array<string, array<string, mixed>>> keyed by the cached-only flag */
    private array $pluginLicenses = [];

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
        $this->pluginLicenses = [];
    }

    public function getConsumer()
    {
        if ($this->consumer === false) {
            $consumer = $this->marketplaceClient->getConsumer();
            // a 200 carrying an empty body reaches here as '', which says nothing about the
            // consumer's licenses; a real answer always carries the list, empty or not
            $this->consumerAvailable = is_array($consumer) && array_key_exists('licenses', $consumer);

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
     * @param bool $cachedOnly Answer from the cached consumer only, returning null when it is cold
     *                         rather than making the caller wait on plugins.matomo.org. For callers
     *                         on a request path - the dashboard promotions - where a synchronous
     *                         request with a 60 second timeout is not acceptable.
     *                         {@link Tasks::warmCacheEntries()} keeps that entry filled for them.
     * @return array<string, array<string, mixed>>|null
     */
    public function getConsumerPluginLicenses(bool $cachedOnly = false): ?array
    {
        $key = (int) $cachedOnly;

        if (!array_key_exists($key, $this->pluginLicenses)) {
            if ($cachedOnly) {
                $consumer = $this->marketplaceClient->getConsumer(true);
                $available = !empty($consumer);
            } else {
                // populates consumerAvailable, so it has to run before the guard below reads it
                $consumer = $this->getConsumer();
                $available = $this->consumerAvailable;
            }

            if (!$available) {
                // Deliberately not remembered: a cache that is cold now may be warm on the
                // next request, and a request that failed is not an answer worth keeping.
                return null;
            }

            $licenses = [];

            if (!empty($consumer['licenses'])) {
                foreach ($consumer['licenses'] as $license) {
                    if (!empty($license['plugin']['name'])) {
                        $licenses[$license['plugin']['name']] = $license;
                    }
                }
            }

            $this->pluginLicenses[$key] = $licenses;
        }

        return $this->pluginLicenses[$key];
    }
}
