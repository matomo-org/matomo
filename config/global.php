<?php

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Piwik\Cache\Eager;

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
        $cacheId = $c->get('cache.eager.cache_id') . 'ui';

        return new Eager($backend, $cacheId);
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

    'observers.global' => array(

        array('Request.dispatch.end', function (ContainerInterface $c) {
            /** @var Eager $cache */
            $cache = $c->get('Piwik\Cache\Eager');
            $cache->persistCacheIfNeeded(43200);
        }),

    ),

    'Piwik\EventDispatcher' => DI\object()
        ->constructorParameter('observers', DI\get('observers.global'))
        ->constructorParameter('container', DI\get('DI\Container'))
);
