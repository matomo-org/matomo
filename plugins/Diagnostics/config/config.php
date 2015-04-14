<?php

return array(
    // Diagnostics for everything that is required for Piwik to run
    'diagnostics.required' => array(
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\PhpVersionCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\DbAdapterCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\PhpExtensionsCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\PhpFunctionsCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\PhpSettingsCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck'),
    ),
    // Diagnostics for recommended features
    'diagnostics.optional' => array(
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\FileIntegrityCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\TrackerCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\TimezoneCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\HttpClientCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\PageSpeedCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\GdExtensionCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedExtensionsCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\RecommendedFunctionsCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\NfsDiskCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingCheck'),
        DI\link('Piwik\Plugins\Diagnostics\Diagnostic\LoadDataInfileCheck'),
    ),

    'Piwik\Plugins\Diagnostics\DiagnosticService' => DI\object()
        ->constructor(DI\link('diagnostics.required'), DI\get('diagnostics.optional')),

    'Piwik\Plugins\Diagnostics\Diagnostic\MemoryLimitCheck' => DI\object()
        ->constructorParameter('minimumMemoryLimit', DI\get('ini.General.minimum_memory_limit')),

    'Piwik\Plugins\Diagnostics\Diagnostic\WriteAccessCheck' => DI\object()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),
);
