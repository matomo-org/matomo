<?php

use Piwik\Container\Container;
use Piwik\Piwik;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\Mock\FakeChangesModel;
use Piwik\Tests\Framework\Mock\TestConfig;

return array(

    // Disable logging
    \Piwik\Log\LoggerInterface::class => \Piwik\DI::decorate(function ($previous, Container $c) {
        $enableLogging = $c->get('ini.tests.enable_logging') == 1 || !empty(getenv('MATOMO_TESTS_ENABLE_LOGGING'));
        if ($enableLogging) {
            return $previous;
        } else {
            return $c->get(\Piwik\Log\NullLogger::class);
        }
    }),

    'Tests.log.allowAllHandlers' => false,

    'log.handlers' => \Piwik\DI::decorate(function ($previous, Container $c) {
        if ($c->get('Tests.log.allowAllHandlers')) {
            return $previous;
        }

        return [
            $c->get('Piwik\Plugins\Monolog\Handler\FileHandler'),
        ];
    }),

    'Matomo\Cache\Backend' => function () {
        return \Piwik\Cache::buildBackend('file');
    },
    'cache.eager.cache_id' => 'eagercache-test-',

    // set in individual tests to override now value when needed
    'Tests.now' => false,

    // Disable loading core translations
    'Piwik\Translation\Translator' => \Piwik\DI::decorate(function ($previous, Container $c) {
        $loadRealTranslations = $c->get('test.vars.loadRealTranslations');
        if (!$loadRealTranslations) {
            return new \Piwik\Translation\Translator($c->get('Piwik\Translation\Loader\LoaderInterface'), $directories = array());
        } else {
            return $previous;
        }
    }),

    'Piwik\Config' => \Piwik\DI::decorate(function ($previous, Container $c) {
        $testingEnvironment = $c->get('Piwik\Tests\Framework\TestingEnvironmentVariables');

        $dontUseTestConfig = $c->get('test.vars.dontUseTestConfig');
        if (!$dontUseTestConfig) {
            $settingsProvider = $c->get('Piwik\Application\Kernel\GlobalSettingsProvider');
            return new TestConfig($settingsProvider, $testingEnvironment, $allowSave = false, $doSetTestEnvironment = true);
        } else {
            return $previous;
        }
    }),

    'Piwik\Access' => \Piwik\DI::decorate(function ($previous, Container $c) {
        $testUseMockAuth = $c->get('test.vars.testUseMockAuth');
        if ($testUseMockAuth) {
            $idSitesAdmin = $c->get('test.vars.idSitesAdminAccess');
            $idSitesView = $c->get('test.vars.idSitesViewAccess');
            $idSitesWrite = $c->get('test.vars.idSitesWriteAccess');
            $idSitesCapabilities = $c->get('test.vars.idSitesCapabilities');
            $fakeIdentity = $c->get('test.vars.fakeIdentity');
            $access = new FakeAccess();

            if (!empty($idSitesView)) {
                FakeAccess::$superUser = false;
                FakeAccess::$idSitesView = $idSitesView;
                FakeAccess::$idSitesWrite = !empty($idSitesWrite) ? $idSitesWrite : array();
                FakeAccess::$idSitesAdmin = !empty($idSitesAdmin) ? $idSitesAdmin : array();
                FakeAccess::$identity = $fakeIdentity ?: 'viewUserLogin';
            } elseif (!empty($idSitesWrite)) {
                FakeAccess::$superUser = false;
                FakeAccess::$idSitesWrite = !empty($idSitesWrite) ? $idSitesWrite : array();
                FakeAccess::$idSitesAdmin = !empty($idSitesAdmin) ? $idSitesAdmin : array();
                FakeAccess::$identity = $fakeIdentity ?: 'writeUserLogin';
            } elseif (!empty($idSitesAdmin)) {
                FakeAccess::$superUser = false;
                FakeAccess::$idSitesAdmin = $idSitesAdmin;
                FakeAccess::$identity = $fakeIdentity ?: 'adminUserLogin';
            } else {
                FakeAccess::$superUser = true;
                FakeAccess::$superUserLogin = $fakeIdentity ?: 'superUserLogin';
                FakeAccess::$identity = $fakeIdentity ?: 'superUserLogin';
            }
            if (!empty($idSitesCapabilities)) {
                FakeAccess::$idSitesCapabilities = (array) $idSitesCapabilities;
            }
            return $access;
        } else {
            return $previous;
        }
    }),

    // Prevent loading plugin changes, so the What's New popup isn't shown
    'Piwik\Changes\Model' => \Piwik\DI::decorate(function ($previous, Container $c) {
        $loadChanges = $c->get('test.vars.loadChanges');
        if (!$loadChanges) {
            return new FakeChangesModel();
        } else {
            return $previous;
        }
    }),

    'observers.global' => \Piwik\DI::add(array(

        array('AssetManager.getStylesheetFiles', \Piwik\DI::value(function (&$stylesheets) {
            $useOverrideCss = \Piwik\Container\StaticContainer::get('test.vars.useOverrideCss');
            if ($useOverrideCss) {
                $stylesheets[] = 'tests/resources/screenshot-override/override.css';
            }
        })),

        array('AssetManager.getJavaScriptFiles', \Piwik\DI::value(function (&$jsFiles) {
            $useOverrideJs = \Piwik\Container\StaticContainer::get('test.vars.useOverrideJs');
            if ($useOverrideJs) {
                $jsFiles[] = 'tests/resources/screenshot-override/override.init.js';
                $jsFiles[] = 'tests/resources/screenshot-override/override.end.js';
            }
        })),

        array('Updater.checkForUpdates', \Piwik\DI::value(function () {
            try {
                @\Piwik\Filesystem::deleteAllCacheOnUpdate();
            } catch (Exception $ex) {
                // pass
            }
        })),

        array('Test.Mail.send', \Piwik\DI::value(function (\PHPMailer\PHPMailer\PHPMailer $mail) {
            $outputFile = PIWIK_INCLUDE_PATH . '/tmp/' . Piwik::getModule() . '.' . Piwik::getAction() . '.mail.json';
            $outputContent = str_replace("=\n", "", $mail->Body ?: $mail->AltBody);
            $outputContent = str_replace("=0A", "\n", $outputContent);
            $outputContent = str_replace("=3D", "=", $outputContent);
            $outputContents = array(
                'from' => $mail->From,
                'to' => $mail->getAllRecipientAddresses(),
                'subject' => $mail->Subject,
                'contents' => $outputContent
            );
            file_put_contents($outputFile, json_encode($outputContents));
        })),
    )),

    'test.vars.forceCliMultiViaCurl' => false,
);
