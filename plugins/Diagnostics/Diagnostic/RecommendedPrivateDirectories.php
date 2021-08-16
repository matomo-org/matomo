<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

class RecommendedPrivateDirectories extends PrivateDirectories
{
    protected $privatePaths = [['tmp/', 'tmp/empty']];
    protected $addError = false;
    protected $labelKey = 'Diagnostics_RecommendedPrivateDirectories';
}

