<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Exception;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\Plugins\InvalidLicenses;

/**
 * The Marketplace API lets you manage your license key so you can download & install in one-click <a target="_blank" rel="noreferrer" href="https://matomo.org/recommends/premium-plugins/">paid premium plugins</a> you have subscribed to.
 *
 * @method static \Piwik\Plugins\Marketplace\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Client
     */
    private $marketplaceClient;

    /**
     * @var Service
     */
    private $marketplaceService;

    /**
     * @var InvalidLicenses
     */
    private $expired;

    public function __construct(Service $service, Client $client, InvalidLicenses $expired)
    {
        $this->marketplaceService = $service;
        $this->marketplaceClient  = $client;
        $this->expired = $expired;
    }

    /**
     * Deletes an existing license key if one is set.
     *
     * @return bool
     */
    public function deleteLicenseKey()
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->setLicenseKey(null);
        return true;
    }

    /**
     * Saves the given license key in case the key is actually valid (exists on the Matomo Marketplace and is not
     * yet expired).
     *
     * @param string $licenseKey
     * @return bool
     *
     * @throws Exception In case of an invalid license key
     * @throws Service\Exception In case of any network problems
     */
    public function saveLicenseKey($licenseKey)
    {
        Piwik::checkUserHasSuperUserAccess();

        $licenseKey = trim($licenseKey);

        // we are currently using the Marketplace service directly to 1) change LicenseKey and 2) not use any cache
        $this->marketplaceService->authenticate($licenseKey);

        try {
            $consumer = $this->marketplaceService->fetch('consumer/validate', array());
        } catch (Api\Service\Exception $e) {
            if ($e->getCode() === Api\Service\Exception::HTTP_ERROR) {
                throw $e;
            }

            $consumer = array();
        }

        if (empty($consumer['isValid'])) {
            throw new Exception(Piwik::translate('Marketplace_ExceptionLinceseKeyIsNotValid'));
        }

        $this->setLicenseKey($licenseKey);

        return true;
    }

    private function setLicenseKey($licenseKey)
    {
        $key = new LicenseKey();
        $key->set($licenseKey);

        $this->marketplaceClient->clearAllCacheEntries();
        $this->expired->clearCache();
    }

}
