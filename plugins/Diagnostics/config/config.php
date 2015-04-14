<?php

return array(
    // Diagnostics for everything that is required for Piwik to run
    'diagnostics.required' => array(
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpVersionCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\DbAdapterCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpExtensionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpFunctionsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\PhpSettingsCheck'),
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck'),
    ),
    // Diagnostics for recommended features
    'diagnostics.optional' => array(
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\FileIntegrityCheck'),
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
        DI\get('Piwik\Plugins\Diagnostics\Diagnostic\LoadDataInfileCheck'),
    ),
    // Allows other plugins to disable diagnostics that were previously registered
    'diagnostics.disabled' => array(),

    'Piwik\Plugins\Diagnostics\DiagnosticService' => DI\object()
        ->constructor(DI\get('diagnostics.required'), DI\get('diagnostics.optional'), DI\get('diagnostics.disabled')),

    'Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck' => DI\object()
        ->constructorParameter('minimumMemoryLimit', DI\get('ini.General.minimum_memory_limit')),

    'Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck' => DI\object()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),
);
