<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;
use Piwik\Updater;

class Updates_5_7_0_b3 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $config = Config::getInstance();
        $generalLocal = $config->getFromLocalConfig('General');

        if (!is_array($generalLocal)) {
            return [];
        }

        $proxyKeys = [
            'proxy_client_headers',
            'proxy_host_headers',
            'proxy_ips',
            'proxy_uri_header',
            'proxy_ip_read_last_in_list',
        ];

        $hasProxyConfig = false;
        foreach ($proxyKeys as $key) {
            if (array_key_exists($key, $generalLocal)) {
                $hasProxyConfig = true;
                break;
            }
        }

        if (
            !$hasProxyConfig
            || array_key_exists('proxy_scheme_headers', $generalLocal)
        ) {
            return [];
        }

        return [
            $this->migration->config->set('General', 'proxy_scheme_headers', [
                'HTTP_X_FORWARDED_PROTO',
                'HTTP_X_FORWARDED_SCHEME',
                'HTTP_X_URL_SCHEME',
            ]),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
