<?php

use Interop\Container\ContainerInterface;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\LicenseKey;

return array(
    'MarketplaceEndpoint' => function (ContainerInterface $c) {
        $domain = 'http://plugins.matomo.org';
        $updater = $c->get('Piwik\Plugins\CoreUpdater\Updater');

        if ($updater->isUpdatingOverHttps()) {
            $domain = str_replace('http://', 'https://', $domain);
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
    }
);
