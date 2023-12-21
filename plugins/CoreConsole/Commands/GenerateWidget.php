<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Widget\WidgetsList;

/**
 */
class GenerateWidget extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:widget')
            ->setDescription('Adds a plugin widget class to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have any widgets defined yet')
            ->addRequiredValueOption('widgetname', null, 'The name of the widget you want to create')
            ->addRequiredValueOption('category', null, 'The name of the category the widget should belong to');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $widgetName = $this->getWidgetName();
        $category   = $this->getCategory();

        if ($category === Piwik::translate($category)) {
            // no translation found...
            $category = $this->makeTranslationIfPossible($pluginName, $category);
        }

        $widgetMethod = $this->getWidgetMethodName($widgetName);
        $widgetClass  = ucfirst($widgetMethod);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin'   => $pluginName,
                                'MyExampleWidget' => $widgetClass,
                                'Example Widget Name' => $this->makeTranslationIfPossible($pluginName, $widgetName),
                                'About Matomo' => $category);
        $whitelistFiles = array('/Widgets', '/Widgets/MyExampleWidget.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('plugins/%s/Widgets/%s.php generated.', $pluginName, $widgetClass),
             'You can now start implementing the <comment>render()</comment> method.',
             'Enjoy!'
        ));

        return self::SUCCESS;
    }

    private function getWidgetMethodName($methodName)
    {
        $methodName = trim($methodName);
        $methodName = str_replace(' ', '', $methodName);
        $methodName = preg_replace("/[^A-Za-z0-9]/", '', $methodName);

        if (0 !== strpos(strtolower($methodName), 'get')) {
            $methodName = 'get' . ucfirst($methodName);
        }

        return lcfirst($methodName);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getWidgetName()
    {
        $validate = function ($widgetName) {
            if (empty($widgetName)) {
                throw new \InvalidArgumentException('Please enter the name of your widget');
            }

            if (preg_match("/[^A-Za-z0-9 ]/", $widgetName)) {
                throw new \InvalidArgumentException('Only alpha numerical characters and whitespaces are allowed');
            }

            return $widgetName;
        };

        $widgetName = $this->getInput()->getOption('widgetname');

        if (empty($widgetName)) {
            $widgetName = $this->askAndValidate(
                'Enter the name of your Widget, for instance "Browser Families": ',
                $validate
            );
        } else {
            $validate($widgetName);
        }

        $widgetName = ucfirst($widgetName);

        return $widgetName;
    }

    protected function getExistingCategories()
    {
        $categories = array();
        foreach (WidgetsList::get()->getWidgetConfigs() as $widget) {
            if ($widget->getCategoryId()) {
                $categories[] = Piwik::translate($widget->getCategoryId());
            }
        }
        $categories = array_values(array_unique($categories));

        return $categories;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getCategory()
    {
        $input = $this->getInput();
        $validate = function ($category) {
            if (empty($category)) {
                throw new \InvalidArgumentException('Please enter the name of the category your widget should belong to');
            }

            return $category;
        };

        $category   = $input->getOption('category');
        $categories = $this->getExistingCategories();

        if (empty($category)) {
            $category = $this->askAndValidate(
                'Enter the widget category, for instance "Visitor" (you can reuse any existing category or define a new one): ',
                $validate,
                null,
                $categories
            );
        } else {
            $validate($category);
        }

        $translationKey = StaticContainer::get('Piwik\Translation\Translator')->findTranslationKeyForTranslation($category);
        if (!empty($translationKey)) {
            return $translationKey;
        }

        $category = ucfirst($category);

        return $category;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getPluginName()
    {
        $pluginNames = $this->getPluginNames();
        $invalidName = 'You have to enter a name of an existing plugin.';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}
