<?php

use Interop\Container\ContainerInterface;
use Piwik\Cache\Eager;

return array(

    'Piwik\Cache\Eager' => function (ContainerInterface $c) {
        $backend = $c->get('Piwik\Cache\Backend');
        $cacheId = $c->get('cache.eager.cache_id') . 'tracker';

        return new Eager($backend, $cacheId);
    },

    'global.observers' => DI\add(array(

        array('Tracker.end', function (ContainerInterface $c) {
            /** @var Eager $cache */
            $cache = $c->get('Piwik\Cache\Eager');
            $cache->persistCacheIfNeeded(43200);
        }),

    )),

);
