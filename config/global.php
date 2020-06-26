<?php

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Piwik\Cache\Eager;
use Piwik\SettingsServer;
use Piwik\Config;

return array(

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

    'DeviceDetector\Cache\Cache' => DI\object('Piwik\DeviceDetector\DeviceDetectorCache')->constructor(86400),

    'observers.global' => array(),

    /**
     * By setting this option to false, the check that the DB schema version matches the version of the source code will be no longer performed.
     * Thus it allows you to execute for example a newer version of Matomo with an older Matomo database version. Please note
     * disabling this setting is not recommended because often an older DB version is not compatible with newer source code.
     * If you disable this setting, make sure to execute the updates after updating the source code. The setting can be useful if
     * you want to update Matomo without any outage when you know the current source code update will still run fine for a short time
     * while in the background the database updates are running.
     */
    'EnableDbVersionCheck' => true,

    'fileintegrity.ignore' => DI\add(array(
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

        $ipsResolved = array();

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $ipsResolved[] = $ip;
            } else {
                $ipFromHost = @gethostbyname($ip);
                if (!empty($ipFromHost)) {
                    $ipsResolved[] = $ipFromHost;
                }
            }
        }

        return $ipsResolved;
    },
    'Zend_Mail_Transport_Abstract' => function () {
        $mailConfig = Config::getInstance()->mail;

        if (empty($mailConfig['host'])
            || $mailConfig['transport'] != 'smtp'
        ) {
            return;
        }

        $smtpConfig = array();
        if (!empty($mailConfig['type'])) {
            $smtpConfig['auth'] = strtolower($mailConfig['type']);
        }

        if (!empty($mailConfig['username'])) {
            $smtpConfig['username'] = $mailConfig['username'];
        }

        if (!empty($mailConfig['password'])) {
            $smtpConfig['password'] = $mailConfig['password'];
        }

        if (!empty($mailConfig['encryption'])) {
            $smtpConfig['ssl'] = $mailConfig['encryption'];
        }

        if (!empty($mailConfig['port'])) {
            $smtpConfig['port'] = $mailConfig['port'];
        }

        $host = trim($mailConfig['host']);
        $transport = new \Zend_Mail_Transport_Smtp($host, $smtpConfig);
        return $transport;
    },

    'Piwik\Tracker\VisitorRecognizer' => DI\object()
        ->constructorParameter('trustCookiesOnly', DI\get('ini.Tracker.trust_visitors_cookies'))
        ->constructorParameter('visitStandardLength', DI\get('ini.Tracker.visit_standard_length'))
        ->constructorParameter('lookbackNSecondsCustom', DI\get('ini.Tracker.window_look_back_for_visitor')),

    'Piwik\Tracker\Settings' => DI\object()
        ->constructorParameter('isSameFingerprintsAcrossWebsites', DI\get('ini.Tracker.enable_fingerprinting_across_websites')),

    'archiving.performance.logger' => null,

    \Piwik\CronArchive\Performance\Logger::class => DI\object()->constructorParameter('logger', DI\get('archiving.performance.logger')),

    \Piwik\Concurrency\LockBackend::class => \DI\get(\Piwik\Concurrency\LockBackend\MySqlLockBackend::class),
);
