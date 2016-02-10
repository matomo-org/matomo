<?php

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Piwik\Cache\Eager;
use Piwik\SettingsServer;

return array(

    'path.root' => PIWIK_USER_PATH,

    'path.tmp' => function (ContainerInterface $c) {
        $root = $c->get('path.root');

        // TODO remove that special case and instead have plugins override 'path.tmp' to add the instance id
        if ($c->has('ini.General.instance_id')) {
            $instanceId = $c->get('ini.General.instance_id');
            $instanceId = $instanceId ? '/' . $instanceId : '';
        } else {
            $instanceId = '';
        }

        return $root . '/tmp' . $instanceId;
    },

    'path.cache' => DI\string('{path.tmp}/cache/tracker/'),

    'Piwik\Cache\Eager' => function (ContainerInterface $c) {
        $backend = $c->get('Piwik\Cache\Backend');
        $cacheId = $c->get('cache.eager.cache_id');

        if (SettingsServer::isTrackerApiRequest()) {
            $eventToPersist = 'Tracker.end';
            $cacheId .= 'tracker';
        } else {
            $eventToPersist = 'Request.dispatch.end';
            $cacheId .= 'ui';
        }

        $cache = new Eager($backend, $cacheId);
        \Piwik\Piwik::addAction($eventToPersist, function () use ($cache) {
            $cache->persistCacheIfNeeded(43200);
        });

        return $cache;
    },
    'Piwik\Cache\Backend' => function (ContainerInterface $c) {
        try {
            $backend = $c->get('ini.Cache.backend');
        } catch (NotFoundException $ex) {
            $backend = 'chained'; // happens if global.ini.php is not available
        }

        return \Piwik\Cache::buildBackend($backend);
    },
    'cache.eager.cache_id' => function () {
        return 'eagercache-' . str_replace(array('.', '-'), '', \Piwik\Version::VERSION) . '-';
    },

    'Psr\Log\LoggerInterface' => DI\object('Psr\Log\NullLogger'),

    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\LoaderCache')
        ->constructor(DI\get('Piwik\Translation\Loader\JsonFileLoader')),

    'observers.global' => array(),

    'Piwik\EventDispatcher' => DI\object()->constructorParameter('observers', DI\get('observers.global')),

    'Zend_Validate_EmailAddress' => function () {
        return new \Zend_Validate_EmailAddress(array(
            'hostname' => new \Zend_Validate_Hostname(array(
                'tld' => false,
            ))));
    },

    'Piwik\Tracker\VisitorRecognizer' => DI\object()
        ->constructorParameter('trustCookiesOnly', DI\get('ini.Tracker.trust_visitors_cookies'))
        ->constructorParameter('visitStandardLength', DI\get('ini.Tracker.visit_standard_length'))
        ->constructorParameter('lookbackNSecondsCustom', DI\get('ini.Tracker.window_look_back_for_visitor'))
        ->constructorParameter('trackerAlwaysNewVisitor', DI\get('ini.Debug.tracker_always_new_visitor')),

    'Piwik\Tracker\Settings' => DI\object()
        ->constructorParameter('isSameFingerprintsAcrossWebsites', DI\get('ini.Tracker.enable_fingerprinting_across_websites')),

);
