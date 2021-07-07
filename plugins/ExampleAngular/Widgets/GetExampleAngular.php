<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleAngular\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

class GetExampleAngular extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ExampleAngular_Example');
        $config->setName('ExampleAngular_ExampleAngular');
        $config->setOrder(99);
        $config->setIsWidgetizable();
    }

    public function render()
    {
        return '<lib-library></lib-library>';
    }

}