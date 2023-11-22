<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ProfessionalServices\Widgets;

use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

abstract class DismissibleWidget extends Widget
{
    public static function getDismissibleWidgetName(): string
    {
        return substr(strrchr(static::class, "\\"), 1);
    }

    public static function exists(string $widgetName): bool
    {
        return class_exists(substr(__CLASS__, 0, strrpos(__CLASS__, "\\")) . "\\" . $widgetName);
    }
}
