<?php

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Piwik\Cache\Eager;
use Piwik\SettingsServer;
use Piwik\Config;

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

        /** @var Piwik\Config\ $config */
        $config = $c->get('Piwik\Config');
        $general = $config->General;
        $tmp = empty($general['tmp_path']) ? '/tmp' : $general['tmp_path'];

        return $root . $tmp . $instanceId;
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
        // If Piwik is not installed yet, it's possible the tmp/ folder is not writable
        // we prevent failing with an unclear message eg. coming from doctrine-cache
        // by forcing to use a cache backend which always works ie. array
        if(!\Piwik\SettingsPiwik::isPiwikInstalled()) {
            $backend = 'array';
        } else {
            try {
                $backend = $c->get('ini.Cache.backend');
            } catch (NotFoundException $ex) {
                $backend = 'chained'; // happens if global.ini.php is not available
            }
        }

        return \Piwik\Cache::buildBackend($backend);
    },
    'cache.eager.cache_id' => function () {
        return 'eagercache-' . str_replace(array('.', '-'), '', \Piwik\Version::VERSION) . '-';
    },

    'entities.idNames' => DI\add(array('idGoal', 'idDimension')),

    'Psr\Log\LoggerInterface' => DI\object('Psr\Log\NullLogger'),

    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\LoaderCache')
        ->constructor(DI\get('Piwik\Translation\Loader\JsonFileLoader')),

    'observers.global' => array(),

    'fileintegrity.ignore' => DI\add(array(
        '*.htaccess',
        '*web.config',
        'bootstrap.php',
        'favicon.ico',
        'robots.txt',
        '.bowerrc',
        '.phpstorm.meta.php',
        'config/config.ini.php',
        'config/config.php',
        'config/common.ini.php',
        'config/*.config.ini.php',
        'config/manifest.inc.php',
        'misc/*.mmdb',
        'misc/*.dat',
        'misc/*.dat.gz',
        'misc/*.bin',
        'misc/user/*png',
        'misc/user/*js',
        'misc/package',
        'misc/package/WebAppGallery/*.xml',
        'misc/package/WebAppGallery/install.sql',
        'plugins/ImageGraph/fonts/unifont.ttf',
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
    )),

    'Piwik\EventDispatcher' => DI\object()->constructorParameter('observers', DI\get('observers.global')),

    'login.whitelist.ips' => function (ContainerInterface $c) {
        /** @var Piwik\Config\ $config */
        $config = $c->get('Piwik\Config');
        $general = $config->General;

        $ips = array();
        if (!empty($general['login_whitelist_ip']) && is_array($general['login_whitelist_ip'])) {
            $ips = $general['login_whitelist_ip'];
        }
        return $ips;
    },

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
