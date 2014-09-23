<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Plugin\Report;
use Piwik\Translate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDimension extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:dimension')
            ->setDescription('Adds a new dimension to an existing plugin. This allows you to persist new values during tracking.')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin which does not have a menu defined yet')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Whether you want to create a "Visit", an "Action" or a "Conversion" dimension')
            ->addOption('dimensionname', null, InputOption::VALUE_REQUIRED, 'A human readable name of the dimension which will be for instance visible in the UI')
            ->addOption('columnname', null, InputOption::VALUE_REQUIRED, 'The name of the column in the MySQL database the dimension will be stored under')
            ->addOption('columntype', null, InputOption::VALUE_REQUIRED, 'The MySQL type for your dimension, for instance "VARCHAR(255) NOT NULL".');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $type          = $this->getDimensionType($input, $output);
        $dimensionName = $this->getDimensionName($input, $output);

        if ('non-tracking-dimension' === $type) {
            $columnName = '';
            $columType  = '';
        } else {
            $columnName = $this->getColumnName($input, $output, $type);
            $columType  = $this->getColumnType($input, $output);
        }

        $dimensionClassName      = $this->getDimensionClassName($dimensionName);
        $translatedDimensionName = $this->makeTranslationIfPossible($pluginName, ucfirst($dimensionName));

        $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExampleTracker';
        $replace       = array('example_action_dimension'  => strtolower($columnName),
                               'example_visit_dimension'   => strtolower($columnName),
                               'example_conversion_dimension'   => strtolower($columnName),
                               'INTEGER(11) DEFAULT 0 NOT NULL' => strtoupper($columType),
                               'VARCHAR(255) DEFAULT NULL'      => strtoupper($columType),
                               'ExampleDimension'       => $dimensionClassName,
                               'ExampleVisitDimension'  => $dimensionClassName,
                               'ExampleActionDimension' => $dimensionClassName,
                               'ExampleConversionDimension'  => $dimensionClassName,
                               'ExampleTracker_DimensionName' => $translatedDimensionName,
                               'ExampleTracker' => $pluginName,
        );

        $whitelistFiles = array('/Columns');

        if ('visit' == $type) {
            $whitelistFiles[] = '/Columns/ExampleVisitDimension.php';
        } elseif ('action' == $type) {
            $whitelistFiles[] = '/Columns/ExampleActionDimension.php';
        } elseif ('conversion' == $type) {
            $whitelistFiles[] = '/Columns/ExampleConversionDimension.php';
        } elseif ('non-tracking-dimension' == $type) {
            $whitelistFiles[] = '/Columns/ExampleDimension.php';
        } else {
            throw new \InvalidArgumentException('This dimension type is not available');
        }

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
            sprintf('Columns/%s.php for %s generated.', ucfirst($dimensionClassName), $pluginName),
            'You should now implement the events within this file',
            'Enjoy!'
        ));
    }

    private function getDimensionClassName($dimensionName)
    {
        $dimensionName = trim($dimensionName);
        $dimensionName = str_replace(' ', '', $dimensionName);
        $dimensionName = preg_replace("/[^A-Za-z0-9]/", '', $dimensionName);

        $dimensionName = ucfirst($dimensionName);

        return $dimensionName;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getDimensionName(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($dimensionName) {
            if (empty($dimensionName)) {
                throw new \InvalidArgumentException('Please enter the name of your dimension');
            }

            if (preg_match("/[^A-Za-z0-9 ]/", $dimensionName)) {
                throw new \InvalidArgumentException('Only alpha numerical characters and whitespaces are allowed');
            }

            return $dimensionName;
        };

        $dimensionName = $input->getOption('dimensionname');

        if (empty($dimensionName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $dimensionName = $dialog->askAndValidate($output, 'Enter a human readable name of your dimension, for instance "Browser": ', $validate);
        } else {
            $validate($dimensionName);
        }

        $dimensionName = ucfirst($dimensionName);

        return $dimensionName;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $type
     * @return array
     * @throws \RuntimeException
     */
    protected function getColumnName(InputInterface $input, OutputInterface $output, $type)
    {
        $validate = function ($columnName) use ($type) {
            if (empty($columnName)) {
                throw new \InvalidArgumentException('Please enter the name of the dimension column');
            }

            if (preg_match("/[^A-Za-z0-9_ ]/", $columnName)) {
                throw new \InvalidArgumentException('Only alpha numerical characters, underscores and whitespaces are allowed');
            }

            if ('visit' == $type) {
                $columns = array_keys(DbHelper::getTableColumns(Common::prefixTable('log_visit')));
            } elseif ('action' == $type) {
                $columns = array_keys(DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action')));
            } elseif ('conversion' == $type) {
                $columns = array_keys(DbHelper::getTableColumns(Common::prefixTable('log_conversion')));
            } else {
                $columns = array();
            }

            foreach ($columns as $column) {
                if (strtolower($column) === strtolower($columnName)) {
                    throw new \InvalidArgumentException('This column name is already in use.');
                }
            }

            return $columnName;
        };

        $columnName = $input->getOption('columnname');

        if (empty($columnName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $columnName = $dialog->askAndValidate($output, 'Enter the name of the column under which it should be stored in the MySQL database, for instance "visit_total_time": ', $validate);
        } else {
            $validate($columnName);
        }

        return $columnName;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getColumnType(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($columnType) {
            if (empty($columnType)) {
                throw new \InvalidArgumentException('Please enter the type of the dimension column');
            }

            return $columnType;
        };

        $columnType = $input->getOption('columntype');

        if (empty($columnType)) {
            $dialog     = $this->getHelperSet()->get('dialog');
            $columnType = $dialog->askAndValidate($output, 'Enter the type of the column under which it should be stored in the MySQL database, for instance "VARCHAR(255) NOT NULL": ', $validate);
        } else {
            $validate($columnType);
        }

        return $columnType;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getDimensionType(InputInterface $input, OutputInterface $output)
    {
        $acceptedValues = array('visit', 'action', 'conversion', 'non-tracking-dimension');

        $validate = function ($type) use ($acceptedValues) {
            if (empty($type) || !in_array($type, $acceptedValues)) {
                throw new \InvalidArgumentException('Please enter a valid dimension type (' . implode(', ', $acceptedValues) .  '). Choose "non-tracking-dimension" if you only need a blank dimension having a name: ');
            }

            return $type;
        };

        $type = $input->getOption('type');

        if (empty($type)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $type = $dialog->askAndValidate($output, 'Please choose the type of dimension you want to create (' . implode(', ', $acceptedValues) .  '). Choose "non-tracking-dimension" if you only need a blank dimension having a name: ', $validate, false, null, $acceptedValues);
        } else {
            $validate($type);
        }

        return $type;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNames();
        $invalidName = 'You have to enter a name of an existing plugin.';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

}
