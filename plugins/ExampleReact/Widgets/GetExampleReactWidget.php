<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleReact\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

class GetExampleReactWidget extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ExampleReact_Example');
        $config->setSubcategoryId('General_Overview');
        $config->setName('ExampleReact_ExampleReactWidget');
        $config->setOrder(99);
    }

    public function render()
    {
        return <<<WIDGET
<div class="react-widget"></div>
<script>$('.react-widget').each(function () {
    if ($(this).data('react-inited')) {
        return;
    }

    window['@matomo/example-react'].ExampleReact.renderTo(this, {name: 'example-name'});

    $(this).data('react-inited', true);
});</script>';
WIDGET;
    }
}