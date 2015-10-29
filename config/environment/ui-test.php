<?php

use Piwik\Container\StaticContainer;

return array(

    // UI tests will remove the port from all URLs to the test server. if a test
    // requires the ports in UI tests (eg, Overlay), add the api/controller methods
    // to one of these whitelists
    'tests.ui.url_normalizer_whitelist.api' => array(),
    'tests.ui.url_normalizer_whitelist.controller' => array(),

    'Piwik\Config' => \DI\decorate(function (\Piwik\Config $config) {
        $config->General['cors_domains'][] = '*';
        $config->trusted_hosts[] = $config->tests['http_host'];
        $config->trusted_hosts[] = $config->tests['http_host'] . ':' . $config->tests['port'];
        return $config;
    }),

    'observers.global' => \DI\add(array(

        // removes port from all URLs to the test Piwik server so UI tests will pass no matter
        // what port is used
        array('Request.dispatch.end', function (&$result) {
            $request = $_GET + $_POST;

            $apiWhitelist = StaticContainer::get('tests.ui.url_normalizer_whitelist.api');
            if (!empty($request['method'])
                && in_array($request['method'], $apiWhitelist)
            ) {
                return;
            }

            $controllerActionWhitelist = StaticContainer::get('tests.ui.url_normalizer_whitelist.controller');
            if (!empty($request['module'])
                && !empty($request['action'])
            ) {
                $controllerAction = $request['module'] . '.' . $request['action'];
                if (in_array($controllerAction, $controllerActionWhitelist)) {
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
            $result = str_replace(realpath(PIWIK_INCLUDE_PATH), '', $result);
        }),

        array('Controller.ExampleRssWidget.rssPiwik.end', function (&$result, $parameters) {
            $result = "";
        }),
    )),

);
