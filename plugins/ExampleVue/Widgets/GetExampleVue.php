<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleVue\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetExampleVue extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ExampleCategory');
        $config->setName('ExampleVue_ExampleVue');
        $config->setOrder(99);
        $config->setIsWidgetizable();
    }

    public function render()
    {
        // using the AngularJS adapter until the Vue migration is complete.
        return '<div example-vue-component></div>';
    }

}