<?php

return array(

    'Piwik\Config' => \DI\decorate(function (\Piwik\Config $config) {
        $config->General['cors_domains'][] = '*';
        return $config;
    }),

    'observers.global' => \DI\add(array(

        array('Request.dispatch.end', function (&$result) {
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
