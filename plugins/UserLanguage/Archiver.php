<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserLanguage;

/**
 * Archiver for UserLanguage Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    public const LANGUAGE_RECORD_NAME = 'UserLanguage_language';
    public const LANGUAGE_DIMENSION = "log_visit.location_browser_lang";
}
