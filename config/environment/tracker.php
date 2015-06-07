<?php

use Interop\Container\ContainerInterface;
use Piwik\Cache\Eager;

return array(

    'Piwik\Cache\Eager' => function (ContainerInterface $c) {
        $backend = $c->get('Piwik\Cache\Backend');
        $cacheId = $c->get('cache.eager.cache_id') . 'tracker';

        $cache = new Eager($backend, $cacheId);
        \Piwik\Piwik::addAction('Tracker.end', function () use ($cache) {
            $cache->persistCacheIfNeeded(43200);
        });
        return $cache;
    },

);
