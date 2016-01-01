<?php

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Piwik\Cache\Eager;
use Piwik\Config;
use Piwik\Db\AdapterFactory;
use Piwik\EventDispatcher;
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

    'db.config' => DI\factory(function (ContainerInterface $c) {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $c->get('Piwik\EventDispatcher');

        /** @var Config $config */
        $config = $c->get('Piwik\Config');

        $dbConfig = $config->database;

        /**
         * Triggered before a database connection is established.
         *
         * This event can be used to change the settings used to establish a connection.
         *
         * @param array &$dbInfos Reference to an array containing database connection info,
         *                        including:
         *
         *                        - **host**: The host name or IP address to the MySQL database.
         *                        - **username**: The username to use when connecting to the
         *                                        database.
         *                        - **password**: The password to use when connecting to the
         *                                       database.
         *                        - **dbname**: The name of the Piwik MySQL database.
         *                        - **port**: The MySQL database port to use.
         *                        - **adapter**: either `'PDO\MYSQL'` or `'MYSQLI'`
         *                        - **type**: The MySQL engine to use, for instance 'InnoDB'
         *
         * TODO: deprecate or remove this event, if db config is in DI, shouldn't need an event to override it in a plugin or test.
         */
        $dispatcher->postEvent('Db.getDatabaseConfig', array(&$dbConfig));

        $dbConfig['profiler'] = $config->Debug['enable_sql_profiler'];
        return $dbConfig;
    })->scope(\DI\Scope::PROTOTYPE),

    'db.adapter.impl' => function (ContainerInterface $c) {
        $dbConfig = $c->get('db.config');

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $c->get('Piwik\Db\AdapterFactory');
        return $adapterFactory->factory($dbConfig['adapter'], $dbConfig);
    },

    'Piwik\Db\AdapterWrapper' => DI\object()
        ->constructorParameter('adapter', DI\get('db.adapter.impl'))
        ->constructorParameter('logger', DI\get('Psr\Log\LoggerInterface'))
        ->constructorParameter('logSqlQueries', DI\get('ini.Debug.log_sql_queries')),

    'Piwik\Db\AdapterInterface' => DI\get('Piwik\Db\AdapterWrapper'),

    'Piwik\Db\SchemaInterface' => function (ContainerInterface $c) {
        $config = $c->get('Piwik\Config');
        $schema = $config->database['schema'];

        // Upgrade from pre 2.0.4
        if (strtolower($schema) == 'myisam'
            || empty($schema)
        ) {
            $schema = 'Mysql';
        }

        $className = str_replace(' ', '\\', ucwords(str_replace('_', ' ', strtolower($schema))));
        $className = 'Piwik\Db\Schema\\' . $className;
        return $c->get($className);
    },

);
