<?php

use Piwik\Config\GeneralConfig;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\LicenseKey;
use Piwik\Container\Container;

return array(
    'MarketplaceEndpoint' => function (Container $c) {

        $domain = 'plugins.matomo.org';

        if (GeneralConfig::getConfigValue('force_matomo_http_request') == 1) {
            return 'http://' . $domain;
        }

        return 'https://' . $domain;
    },
    'Piwik\Plugins\Marketplace\Api\Service' => function (Container $c) {
        /** @var Service $previous */

        $domain = $c->get('MarketplaceEndpoint');

        $service = new Service($domain);

        $key = new LicenseKey();
        $accessToken = $key->get();

        $service->authenticate($accessToken);

        return $service;
    },
);
