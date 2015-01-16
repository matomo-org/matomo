<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// tiny script to get plugin version from plugin.json from a bash script
require_once __DIR__ . '/../../core/Version.php';

$pluginName = $argv[1];
$pluginJsonPath = __DIR__ . "/../../plugins/$pluginName/plugin.json";

$pluginJsonContents = file_get_contents($pluginJsonPath);
$pluginJsonContents = json_decode($pluginJsonContents, true);

$minimumRequiredPiwik = @$pluginJsonContents["require"]["piwik"];

if (empty($minimumRequiredPiwik)) {
    $minimumRequiredPiwik = "master";
} else {
    if (!preg_match("/^[^0-9]*(.*)/", $minimumRequiredPiwik, $matches)
        || empty($matches[1])
        || version_compare($matches[1], \Piwik\Version::VERSION) > 0
    ) {
        $minimumRequiredPiwik = "master";
    } else {
        $minimumRequiredPiwik = $matches[1];
    }
}

echo $minimumRequiredPiwik;