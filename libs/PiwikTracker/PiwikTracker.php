<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

// PiwikTracker.php is now managed by composer. To prevent breaking existing
// code, this file has been left as a redirect to its new location in the
// vendor directory.
if (!class_exists('PiwikTracker')) {
    require_once __DIR__ . '/../../vendor/matomo/matomo-php-tracker/PiwikTracker.php';
}

if (PiwikTracker::VERSION !== 1) {
    throw new Exception("Expected PiwikTracker in libs/PiwikTracker/PiwikTracker.php to be version 1 for keeping backward compatibility.");
}
