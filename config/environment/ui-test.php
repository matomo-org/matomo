<?php

use Piwik\Container\StaticContainer;
use Piwik\Plugins\Diagnostics\Diagnostic\FileIntegrityCheck;
use Piwik\Plugins\Diagnostics\Diagnostic\PhpVersionCheck;
use Piwik\Plugins\Diagnostics\Diagnostic\RequiredPrivateDirectories;

return [

    // UI tests will remove the port from all URLs to the test server. if a test
    // requires the ports in UI tests (eg, Overlay), add the api/controller methods
    // to one of these blacklists
    'tests.ui.url_normalizer_blacklist.api' => [],
    'tests.ui.url_normalizer_blacklist.controller' => [],

    'twig.cache' => function (\Psr\Container\ContainerInterface $container) {
        $templatesPath = $container->get('path.tmp.templates');
        return new class($templatesPath) extends \Twig\Cache\FilesystemCache {
            public function write(string $key, string $content): void
            {
                $retryCount = 3;

                $attempts = 0;
                while ($attempts < $retryCount) {
                    try {
                        parent::write($key, $content);
                        return;
                    } catch (\Exception $ex) {
                        if (!preg_match('/^Failed to write cache file/', $ex->getMessage())) {
                            throw $ex;
                        }

                        usleep(50);
                        ++$attempts;
                    }
                }
            }
        };
    },

    'Piwik\Config' => \DI\decorate(function (\Piwik\Config $config, \Psr\Container\ContainerInterface $c) {
        $config->General['cors_domains'][] = '*';
        $config->General['trusted_hosts'][] = '127.0.0.1';
        $config->General['trusted_hosts'][] = $config->tests['http_host'];
        $config->General['trusted_hosts'][] = $config->tests['http_host'] . ':' . $config->tests['port'];

        // disable plugin promos for UI tests, only enable when explicitly requested
        if ($c->get('test.vars.enableProfessionalSupportAdsForUITests')) {
            $config->General['piwik_professional_support_ads_enabled'] = '1';
        } else {
            $config->General['piwik_professional_support_ads_enabled'] = '0';
        }

        return $config;
    }),

    'observers.global' => \DI\add([

        // removes port from all URLs to the test Piwik server so UI tests will pass no matter
        // what port is used
        ['Request.dispatch.end', DI\value(function (&$result) {
            $request = $_GET + $_POST;

            $apiblacklist = StaticContainer::get('tests.ui.url_normalizer_blacklist.api');
            if (!empty($request['method'])
                && in_array($request['method'], $apiblacklist)
            ) {
                return;
            }

            $controllerActionblacklist = StaticContainer::get('tests.ui.url_normalizer_blacklist.controller');
            if (!empty($request['module'])) {
                $controllerAction = $request['module'] . '.' . (isset($request['action']) ? $request['action'] : 'index');
                if (in_array($controllerAction, $controllerActionblacklist)) {
                    return;
                }
            }

            $config = \Piwik\Config::getInstance();
            $host = $config->tests['http_host'];
            $port = $config->tests['port'];

            if (!empty($port)) {
                // remove the port from URLs if any so UI tests won't fail if the port isn't 80
                $result = str_replace($host . ':' . $port, $host, $result);
            }

            // remove PIWIK_INCLUDE_PATH from result so tests don't change based on the machine used
            $result = str_replace(realpath(PIWIK_INCLUDE_PATH), '', $result ?? '');
        })],

        ['Controller.RssWidget.rssPiwik.end', DI\value(function (&$result, $parameters) {
            $result = '';
        })],

        \Piwik\Tests\Framework\XssTesting::getJavaScriptAddEvent(),
    ]),

    // disable some diagnostics for UI tests
    'diagnostics.disabled'  => \DI\add([
        \DI\get(FileIntegrityCheck::class),
        \DI\get(RequiredPrivateDirectories::class),
        \DI\get(PhpVersionCheck::class),
    ]),
];
