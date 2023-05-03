<?php

use Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingLastRunCheck;
use Piwik\Plugins\Diagnostics\Diagnostic\RequiredPrivateDirectories;
use Piwik\Plugins\Diagnostics\Diagnostic\RecommendedPrivateDirectories;

return array(
    // Diagnostics for everything that is required for Piwik to run
    'diagnostics.required' => array(
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PhpVersionCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DbAdapterCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DbReaderCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PhpExtensionsCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PhpFunctionsCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PhpSettingsCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck'),
    ),
    // Diagnostics for recommended features
    'diagnostics.optional' => array(
        Piwik\DI::get(RequiredPrivateDirectories::class),
        Piwik\DI::get(RecommendedPrivateDirectories::class),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\FileIntegrityCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PHPBinaryCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\TrackerCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\TimezoneCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\HttpClientCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PageSpeedCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\GdExtensionCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedExtensionsCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedFunctionsCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\NfsDiskCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingCheck'),
        Piwik\DI::get(CronArchivingLastRunCheck::class),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DatabaseAbilitiesCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DbOverSSLCheck'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DbMaxPacket'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\ForceSSLCheck'),
    ),
    'diagnostics.informational' => array(
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\MatomoInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\PhpInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\DatabaseInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\ConfigInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\ServerInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\ReportInformational'),
        Piwik\DI::get('Piwik\Plugins\Diagnostics\Diagnostic\UserInformational'),
        Piwik\DI::get(\Piwik\Plugins\Diagnostics\Diagnostic\ArchiveInvalidationsInformational::class),
    ),
    // Allows other plugins to disable diagnostics that were previously registered
    'diagnostics.disabled' => array(),

    'Piwik\Plugins\Diagnostics\DiagnosticService' => Piwik\DI::autowire()
        ->constructor(Piwik\DI::get('diagnostics.required'), Piwik\DI::get('diagnostics.optional'), Piwik\DI::get('diagnostics.informational'), Piwik\DI::get('diagnostics.disabled')),

    'Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck' => Piwik\DI::autowire()
        ->constructorParameter('minimumMemoryLimit', Piwik\DI::get('ini.General.minimum_memory_limit')),

    'Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck' => Piwik\DI::autowire()
        ->constructorParameter('tmpPath', Piwik\DI::get('path.tmp')),
);
