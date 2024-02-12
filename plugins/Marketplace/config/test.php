<?php

use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\LicenseKey;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Consumer as MockConsumer;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as MockService;
use Piwik\Container\Container;

return array(
    'MarketplaceEndpoint' => function (Container $c) {
        // if you wonder why this here is configured here again, and the same as in `config.php`,
        // it is because someone might have overwritten MarketplaceEndpoit in local config.php and we want
        // to make sure system tests of marketplace are ran against plugins.piwik.org
        $domain = 'http://plugins.piwik.org';

        if (\Piwik\Http::isUpdatingOverHttps()) {
            $domain = str_replace('http://', 'https://', $domain);
        }

        return $domain;
    },
    'Piwik\Plugins\Marketplace\Consumer' => function (Container $c) {
        $consumerTest = $c->get('test.vars.consumer');
        $licenseKey = new LicenseKey();

        if ($consumerTest == 'validLicense') {
            $consumer = MockConsumer::buildValidLicense();
            $licenseKey->set('123456789');
        } elseif ($consumerTest == 'exceededLicense') {
            $consumer = MockConsumer::buildExceededLicense();
            $licenseKey->set('1234567891');
        } elseif ($consumerTest == 'expiredLicense') {
            $consumer = MockConsumer::buildExpiredLicense();
            $licenseKey->set('1234567892');
        } else {
            $consumer = MockConsumer::buildNoLicense();
            $licenseKey->set(null);
        }

        return $consumer;
    },
    'Piwik\Plugins\Marketplace\Plugins' => Piwik\DI::decorate(function ($previous, Container $c) {
        /** @var \Piwik\Plugins\Marketplace\Plugins $previous */
        $previous->setPluginsHavingUpdateCache(null);

        $pluginNames = $c->get('test.vars.mockMarketplaceAssumePluginNamesActivated');

        if (!empty($pluginNames)) {
            /** @var \Piwik\Plugins\Marketplace\Plugins $previous */
            $previous->setActivatedPluginNames($pluginNames);
        }

        return $previous;
    }),
    'Piwik\Plugins\Marketplace\Api\Client' => Piwik\DI::decorate(function ($previous) {
        /** @var \Piwik\Plugins\Marketplace\Api\Client $previous */
        $previous->clearAllCacheEntries();

        return $previous;
    }),
    'Piwik\Plugins\Marketplace\Plugins\InvalidLicenses' => Piwik\DI::decorate(function ($previous, Container $c) {

        $pluginNames = $c->get('test.vars.mockMarketplaceAssumePluginNamesActivated');

        if (!empty($pluginNames)) {
            /** @var \Piwik\Plugins\Marketplace\Plugins\InvalidLicenses $previous */
            $previous->setActivatedPluginNames($pluginNames);
            $previous->clearCache();
        }

        return $previous;
    }),
    'Piwik\Plugins\Marketplace\Api\Service' => Piwik\DI::decorate(function ($previous, Container $c) {
        if (!$c->get('test.vars.mockMarketplaceApiService')) {
            return $previous;
        }

        // for ui tests
        $service = new MockService();

        $key = new LicenseKey();
        $accessToken = $key->get();

        $service->authenticate($accessToken);

        // remove shop review embed URL and convert cover image URLs to local ones
        function updatePluginUrlsForTests(&$plugin)
        {
            if (!empty($plugin['shop']['reviews']['embedUrl'])) {
                $plugin['shop']['reviews']['embedUrl'] = '';
            }

            if (!empty($plugin['coverImage'])) {
                $plugin['coverImage'] = preg_replace(
                    [
                        '@^https?://.*?/([^/]*?)/images/([^/]*?)/(.*?)$@',
                        '@^https?://.*?/img/categories/(.*?)$@i',
                    ],
                    [
                        'plugins/Marketplace/tests/resources/images/plugins/$1/images/$2/$3',
                        'plugins/Marketplace/tests/resources/images/categories/$1',
                    ],
                    $plugin['coverImage'],
                    1
                );
            }
        }

        // update URLs in production-like mock API response fixtures so that images work in tests
        // caters for a list of plugins as well as a single plugin payload content
        function updateUrlsInFixtureContent($content)
        {
            $content = json_decode($content, true);
            if (!empty($content['plugins'])) {
                foreach ($content['plugins'] as &$plugin) {
                    updatePluginUrlsForTests($plugin);
                }
            } else {
                updatePluginUrlsForTests($content);
            }
            return json_encode($content);
        }

        $isExceededUser = $c->get('test.vars.consumer') === 'exceededLicense';
        $isExpiredUser = $c->get('test.vars.consumer') === 'expiredLicense';
        $isValidUser = $c->get('test.vars.consumer') === 'validLicense';
        $startFreeTrialSuccess = $c->get('test.vars.startFreeTrialSuccess');

        $service->setOnDownloadCallback(function ($action, $params) use ($service, $isExceededUser, $isValidUser, $isExpiredUser, $startFreeTrialSuccess) {
            if ($action === 'info') {
                return $service->getFixtureContent('v2.0_info.json');
            } elseif ($action === 'consumer' && $service->getAccessToken() === 'valid') {
                return $service->getFixtureContent('v2.0_consumer-access_token-consumer2_paid1.json');
            } elseif ($action === 'consumer/validate' && $service->getAccessToken() === 'valid') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-consumer2_paid1.json');
            } elseif ($action === 'consumer' && $service->getAccessToken() === 'invalid') {
                return $service->getFixtureContent('v2.0_consumer-access_token-notexistingtoken.json');
            } elseif ($action === 'consumer/validate' && $service->getAccessToken() === 'invalid') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-notexistingtoken.json');
            } elseif ($action === 'plugins' && empty($params['purchase_type']) && empty($params['query'])) {
                $content = $service->getFixtureContent('v2.0_plugins.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins' && $isExceededUser && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID && empty($params['query'])) {
                $content = $service->getFixtureContent('v2.0_plugins-purchase_type-paid-num_users-201-access_token-consumer2_paid1.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins' && $isExpiredUser && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID && empty($params['query'])) {
                $content = $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-consumer1_paid2_custom1.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins' && ($service->hasAccessToken() || $isValidUser) && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID && empty($params['query'])) {
                $content = $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-consumer2_paid1.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins' && !$service->hasAccessToken() && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID && empty($params['query'])) {
                $content = $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-notexistingtoken.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'themes' && empty($params['purchase_type']) && empty($params['query'])) {
                return $service->getFixtureContent('v2.0_themes.json');
            } elseif ($action === 'plugins/Barometer/info') {
                $content = $service->getFixtureContent('v2.0_plugins_Barometer_info.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins/TreemapVisualization/info') {
                $content = $service->getFixtureContent('v2.0_plugins_TreemapVisualization_info.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins/PaidPlugin1/info' && $service->hasAccessToken() && $isExceededUser) {
                $content = $service->getFixtureContent('v2.0_plugins_PaidPlugin1_info-purchase_type-paid-num_users-201-access_token-consumer2_paid1.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins/PaidPlugin1/info' && $service->hasAccessToken()) {
                $content = $service->getFixtureContent('v2.0_plugins_PaidPlugin1_info-access_token-consumer3_paid1_custom2.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins/PaidPlugin1/info' && !$service->hasAccessToken()) {
                $content = $service->getFixtureContent('v2.0_plugins_PaidPlugin1_info.json');
                return updateUrlsInFixtureContent($content);
            } elseif ($action === 'plugins/PaidPlugin1/freeTrial') {
                // this endpoint should only be called with "$getExtendedInfo = true"
                return [
                    'status' => $startFreeTrialSuccess ? 201 : 400,
                    'headers' => [],
                    'data' => '',
                ];
            } elseif ($action === 'plugins/checkUpdates') {
                return $service->getFixtureContent('v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json');
            }
        });

        return $service;
    })
);
