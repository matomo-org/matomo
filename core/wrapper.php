<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/.."));
}

require_once PIWIK_INCLUDE_PATH . '/core/Common.php';

if (!Piwik\Common::isPhpCliMode()) {
    return;
}

Piwik\Common::$isCliMode = false;

require_once PIWIK_INCLUDE_PATH . "/index.php";
