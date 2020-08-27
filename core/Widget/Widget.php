<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;
use Piwik\View;

/**
 * Defines a new widget. You can create a new widget using the console command `./console generate:widget`.
 * The generated widget will guide you through the creation of a widget.
 *
 * For an example, see {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets/MyExampleWidget.php}
 *
 * @api since Piwik 3.0.0
 */
class Widget
{
    /**
     * @param WidgetConfig $config
     * @api
     */
    public static function configure(WidgetConfig $config)
    {
    }

    /**
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * Assigns the given variables to the template and renders it.
     *
     * Example:
     *
     *     public function myControllerAction () {
     *        return $this->renderTemplate('index', array(
     *            'answerToLife' => '42'
     *        ));
     *     }
     *
     * This will render the 'index.twig' file within the plugin templates folder and assign the view variable
     * `answerToLife` to `42`.
     *
     * @param string $template   The name of the template file. If only a name is given it will automatically use
     *                           the template within the plugin folder. For instance 'myTemplate' will result in
     *                           '@$pluginName/myTemplate.twig'. Alternatively you can include the full path:
     *                           '@anyOtherFolder/otherTemplate'. The trailing '.twig' is not needed.
     * @param array $variables   For instance array('myViewVar' => 'myValue'). In template you can use {{ myViewVar }}
     * @return string
     * @api
     */
    protected function renderTemplate($template, array $variables = array())
    {
        if (false === strpos($template, '@') || false === strpos($template, '/')) {
            $aPluginName = explode('\\', get_class($this));
            $aPluginName = $aPluginName[2];
            $template = '@' . $aPluginName . '/' . $template;
        }

        $view = new View($template);

        foreach ($variables as $key => $value) {
            $view->$key = $value;
        }

        return $view->render();
    }

}
