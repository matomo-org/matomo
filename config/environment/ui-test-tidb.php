<?php

use Piwik\DI;

return [
    'observers.global' => DI::add([
        ['API.ScheduledReports.getReports.end', DI::value(function (&$result, $parameters) {
            // TiDb uses a different collation, causing the report order to be slightly different.
            // To have a consistent sorting, we manually sort here in PHP again.
            usort($result, function ($a, $b) {
                return strcmp($a['description'], $b['description']);
            });
        })],
    ]),
];
