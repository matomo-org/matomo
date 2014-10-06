<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Piwik;
use Piwik\Plugin\Widgets;
use Piwik\Translate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateWidget extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:widget')
            ->setDescription('Adds a plugin widget class to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin which does not have any widgets defined yet')
            ->addOption('category', null, InputOption::VALUE_REQUIRED, 'The name of the category the widget should belong to');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $category   = $this->getCategory($input, $output);

        if ($category === Piwik::translate($category)) {
            // no translation found...
            $category = $this->makeTranslationIfPossible($pluginName, $category);
        }

        $exampleFolder  = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
        $replace        = array('ExamplePlugin'    => $pluginName,
                                'Example Category' => $category);
        $whitelistFiles = array('/Widgets.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('Widgets.php for %s generated.', $pluginName),
             'You can now start defining your plugin widgets',
             'Enjoy!'
        ));
    }

    protected function getExistingCategories()
    {
        $categories = array();
        foreach (Widgets::getAllWidgets() as $widget) {
            if ($widget->getCategory()) {
                $categories[] = Piwik::translate($widget->getCategory());
            }
        }
        $categories = array_values(array_unique($categories));

        return $categories;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getCategory(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($category) {
            if (empty($category)) {
                throw new \InvalidArgumentException('Please enter the name of the category your widget should belong to');
            }

            return $category;
        };

        $category   = $input->getOption('category');
        $categories = $this->getExistingCategories();

        if (empty($category)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $category = $dialog->askAndValidate($output, 'Enter the widget category, for instance "Visitor" (you can reuse any existing category or define a new one): ', $validate, false, null, $categories);
        } else {
            $validate($category);
        }

        $translationKey = Translate::findTranslationKeyForTranslation($category);
        if (!empty($translationKey)) {
            return $translationKey;
        }

        $category = ucfirst($category);

        return $category;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Widgets.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have any widgets defined';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

}
