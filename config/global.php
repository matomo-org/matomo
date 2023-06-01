<?php

use Psr\Container\ContainerInterface;
use Matomo\Cache\Eager;
use Piwik\SettingsServer;

return [

    'path.root' => PIWIK_DOCUMENT_ROOT,

    'path.misc.user' => 'misc/user/',

    'path.tmp' => function (ContainerInterface $c) {
        $root = PIWIK_USER_PATH;

        // TODO remove that special case and instead have plugins override 'path.tmp' to add the instance id
        if ($c->has('ini.General.instance_id')) {
            $instanceId = $c->get('ini.General.instance_id');
            $instanceId = $instanceId ? '/' . $instanceId : '';
        } else {
            $instanceId = '';
        }

        /** @var Piwik\Config\ $config */
        $config = $c->get('Piwik\Config');
        $general = $config->General;
        $tmp = empty($general['tmp_path']) ? '/tmp' : $general['tmp_path'];

        return $root . $tmp . $instanceId;
    },

    'path.tmp.templates' => DI\string('{path.tmp}/templates_c'),

    'path.cache' => DI\string('{path.tmp}/cache/tracker/'),

    'view.clearcompiledtemplates.enable' => true,

    'twig.cache' => DI\string('{path.tmp.templates}'),

    'Matomo\Cache\Eager' => function (ContainerInterface $c) {
        $backend = $c->get('Matomo\Cache\Backend');
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
    'Matomo\Cache\Backend' => function (ContainerInterface $c) {
        // If Piwik is not installed yet, it's possible the tmp/ folder is not writable
        // we prevent failing with an unclear message eg. coming from doctrine-cache
        // by forcing to use a cache backend which always works ie. array
        if (!\Piwik\SettingsPiwik::isMatomoInstalled()) {
            $backend = 'array';
        } else {
            try {
                $backend = $c->get('ini.Cache.backend');
            } catch (\DI\NotFoundException $ex) {
                $backend = 'chained'; // happens if global.ini.php is not available
            }
        }

        return \Piwik\Cache::buildBackend($backend);
    },
    'cache.eager.cache_id' => function () {
        return 'eagercache-' . str_replace(['.', '-'], '', \Piwik\Version::VERSION) . '-';
    },

    /**
     * A list of API query parameters that map to entity IDs, for example, `idGoal` for goals.
     *
     * If your plugin introduces new entities that can be fetched or manipulated by ID through
     * API requests, you should add the query parameters that represent the new entity's IDs
     * to this array.
     */
    'entities.idNames' => DI\add(['idGoal', 'idDimension']),

    /**
     * If your plugin uses custom query parameters in API requests (that is, query parameters not used
     * by a core plugin), and you want to be able to use those query parameters in system tests, you
     * will need to add them, via DI, to this array. Otherwise, in system tests, they will be
     * silently ignored.
     *
     * Note: if the query parameter has been added to `'entities.idNames'`, it does not need to be added
     * here as well.
     */
    'DocumentationGenerator.customParameters' => [],

    'Psr\Log\LoggerInterface' => DI\create('Psr\Log\NullLogger'),

    'Piwik\Translation\Loader\LoaderInterface' => DI\autowire('Piwik\Translation\Loader\LoaderCache')
        ->constructorParameter('loader', DI\get('Piwik\Translation\Loader\JsonFileLoader')),

    'DeviceDetector\Cache\Cache' => DI\autowire('Piwik\DeviceDetector\DeviceDetectorCache')->constructor(86400),

    'observers.global' => [],

    /**
     * By setting this option to false, the check that the DB schema version matches the version of the source code will
     * be no longer performed. Thus it allows you to execute for example a newer version of Matomo with an older Matomo
     * database version. Please note disabling this setting is not recommended because often an older DB version is not
     * compatible with newer source code.
     * If you disable this setting, make sure to execute the updates after updating the source code. The setting can be
     * useful if you want to update Matomo without any outage when you know the current source code update will still
     * run fine for a short time while in the background the database updates are running.
     */
    'EnableDbVersionCheck' => true,

    'fileintegrity.ignore' => DI\add([
        '*.htaccess',
        '*web.config',
        'bootstrap.php',
        'favicon.ico',
        'robots.txt',
        '.bowerrc',
        '.lfsconfig',
        '.phpstorm.meta.php',
        'config/config.ini.php',
        'config/config.php',
        'config/common.ini.php',
        'config/*.config.ini.php',
        'config/manifest.inc.php',
        'misc/*.dat',
        'misc/*.dat.gz',
        'misc/*.mmdb',
        'misc/*.mmdb.gz',
        'misc/*.bin',
        'misc/user/*png',
        'misc/user/*svg',
        'misc/user/*js',
        'misc/user/*/config.ini.php',
        'misc/package',
        'misc/package/WebAppGallery/*.xml',
        'misc/package/WebAppGallery/install.sql',
        'plugins/ImageGraph/fonts/unifont.ttf',
        'plugins/*/config/tracker.php',
        'plugins/*/config/config.php',
        'vendor/autoload.php',
        'vendor/composer/autoload_real.php',
        'vendor/szymach/c-pchart/app/*',
        'tmp/*',
        // Search engine sites verification
        'google*.html',
        'BingSiteAuth.xml',
        'yandex*.html',
        // common files on shared hosters
        'php.ini',
        '.user.ini',
        'error_log',
        // Files below are not expected but they used to be present in older Piwik versions and may be still here
        // As they are not going to cause any trouble we won't report them as 'File to delete'
        '*.coveralls.yml',
        '*.scrutinizer.yml',
        '*.gitignore',
        '*.gitkeep',
        '*.gitmodules',
        '*.gitattributes',
        '*.bower.json',
        '*.travis.yml',
    ]),

    'Piwik\EventDispatcher' => DI\autowire()->constructorParameter('observers', DI\get('observers.global')),

    'login.allowlist.ips' => function (ContainerInterface $c) {
        /** @var Piwik\Config\ $config */
        $config = $c->get('Piwik\Config');
        $general = $config->General;

        $ips = [];
        if (!empty($general['login_allowlist_ip']) && is_array($general['login_allowlist_ip'])) {
            $ips = $general['login_allowlist_ip'];
        } elseif (!empty($general['login_whitelist_ip']) && is_array($general['login_whitelist_ip'])) {
            // for BC
            $ips = $general['login_whitelist_ip'];
        }

        $ipsResolved = [];

        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP) || \Matomo\Network\IPUtils::getIPRangeBounds($ip) !== null) {
                $ipsResolved[] = $ip;
            } else {
                $lazyCache = \Piwik\Cache::getLazyCache();
                $cacheKey = 'DNS.' . md5($ip);

                $resolvedIps = $lazyCache->fetch($cacheKey);

                if (!is_array($resolvedIps)) {
                    $resolvedIps = [];

                    $ipFromHost = @gethostbyname($ip);
                    if (!empty($ipFromHost) && $ipFromHost !== $ip) {
                        $resolvedIps[] = $ipFromHost;
                    }

                    if (function_exists('dns_get_record')) {
                        $entry = @dns_get_record($ip, DNS_AAAA);

                        if (
                            !empty($entry['0']['ipv6'])
                            && filter_var($entry['0']['ipv6'], FILTER_VALIDATE_IP)
                        ) {
                            $resolvedIps[] = $entry['0']['ipv6'];
                        }
                    }

                    $lazyCache->save($cacheKey, $resolvedIps, 30);
                }

                $ipsResolved = array_merge($ipsResolved, $resolvedIps);
            }
        }

        return $ipsResolved;
    },

    /**
     * This defines a list of hostnames Matomo's Http class will deny requests to. Wildcards (*) can be used in the
     * beginning to match any subdomain level or in the end to match any tlds
     */
    'http.blocklist.hosts' => [
        '*.amazonaws.com',
    ],

    'Piwik\Tracker\VisitorRecognizer' => DI\autowire()
        ->constructorParameter('trustCookiesOnly', DI\get('ini.Tracker.trust_visitors_cookies'))
        ->constructorParameter('visitStandardLength', DI\get('ini.Tracker.visit_standard_length'))
        ->constructorParameter('lookbackNSecondsCustom', DI\get('ini.Tracker.window_look_back_for_visitor')),

    'Piwik\Tracker\Settings' => DI\autowire()
        ->constructorParameter(
            'isSameFingerprintsAcrossWebsites',
            DI\get('ini.Tracker.enable_fingerprinting_across_websites')
        ),

    'archiving.performance.logger' => null,

    \Piwik\CronArchive\Performance\Logger::class => DI\autowire()
        ->constructorParameter('logger', DI\get('archiving.performance.logger')),

    \Piwik\Concurrency\LockBackend::class => \DI\get(\Piwik\Concurrency\LockBackend\MySqlLockBackend::class),

    \Piwik\Segment\SegmentsList::class => function () {
        return \Piwik\Segment\SegmentsList::get();
    }
];
