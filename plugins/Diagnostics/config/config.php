<?php

use Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingLastRunCheck;
use Piwik\Plugins\Diagnostics\Diagnostic\RequiredPrivateDirectories;
use Piwik\Plugins\Diagnostics\Diagnostic\RecommendedPrivateDirectories;

return array(
    // Diagnostics for everything that is required for Piwik to run
    'diagnostics.required' => array(
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpVersionCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DbAdapterCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DbReaderCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpExtensionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpFunctionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpSettingsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck'),
    ),
    // Diagnostics for recommended features
    'diagnostics.optional' => array(
        DI\get(RequiredPrivateDirectories::class),
        DI\get(RecommendedPrivateDirectories::class),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\FileIntegrityCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PHPBinaryCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\TrackerCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\TimezoneCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\HttpClientCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PageSpeedCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\GdExtensionCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedExtensionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedFunctionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\NfsDiskCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingCheck'),
        DI\get(CronArchivingLastRunCheck::class),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DatabaseAbilitiesCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DbOverSSLCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DbMaxPacket'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\ForceSSLCheck'),
    ),
    'diagnostics.informational' => array(
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\MatomoInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DatabaseInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\ConfigInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\ServerInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\ReportInformational'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\UserInformational'),
        DI\get(\Piwik\Plugins\Diagnostics\Diagnostic\ArchiveInvalidationsInformational::class),
    ),
    // Allows other plugins to disable diagnostics that were previously registered
    'diagnostics.disabled' => array(),

    'Piwik\Plugins\Diagnostics\DiagnosticService' => DI\autowire()
        ->constructor(DI\get('diagnostics.required'), DI\get('diagnostics.optional'), DI\get('diagnostics.informational'), DI\get('diagnostics.disabled')),

    'Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck' => DI\autowire()
        ->constructorParameter('minimumMemoryLimit', DI\get('ini.General.minimum_memory_limit')),

    'Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck' => DI\autowire()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),
);
