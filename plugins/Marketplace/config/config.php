<?php

use Piwik\Http;
use Psr\Container\ContainerInterface;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\LicenseKey;

return array(
    'MarketplaceEndpoint' => function (ContainerInterface $c) {
        $domain = 'https://plugins.matomo.org';

        if (!Http::isUpdatingOverHttps()) {
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
    'diagnostics.optional' => DI\add(array(
      DI\get('Piwik\Plugins\CoreUpdater\Diagnostic\HttpsUpdateCheck'),
    )),
);
