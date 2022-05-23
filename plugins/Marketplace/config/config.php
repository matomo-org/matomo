<?php

use Piwik\Config\GeneralConfig;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\LicenseKey;
use Psr\Container\ContainerInterface;

return array(
    'MarketplaceEndpoint' => function (ContainerInterface $c) {
        $domain = 'https://plugins.matomo.org';

        if (GeneralConfig::getConfigValue('force_matomo_ssl_request') === 0) {
            $domain = str_replace('https://', 'http://', $domain);
        }

        return $domain;
    },
    'Piwik\Plugins\Marketplace\Api\Service' => function (ContainerInterface $c) {
        /** @var \Piwik\Plugins\Marketplace\Api\Service $previous */

        $domain = $c->get('MarketplaceEndpoint');

        $service = new Service($domain);

        $key = new LicenseKey();
        $accessToken = $key->get();

        $service->authenticate($accessToken);

        return $service;
    },
);
