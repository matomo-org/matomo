<?php

use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\LicenseKey;
use Psr\Container\ContainerInterface;

return array(
    'MarketplaceEndpoint' => function (ContainerInterface $c) {
        return 'http://plugins.matomo.org';
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
