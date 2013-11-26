<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class GenerateTest extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:test')
            ->setDescription('Adds a test to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
            ->addOption('testname', null, InputOption::VALUE_REQUIRED, 'The name of the test to create')
            ->addOption('testtype', null, InputOption::VALUE_REQUIRED, 'Whether you want to create a "unit", "integration" or "database" test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $testName   = $this->getTestName($input, $output);
        $testType = $this->getTestType($input, $output);

        $exampleFolder  = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
        $replace        = array(
            'ExamplePlugin'               => $pluginName,
            'SimpleTest'                  => $testName,
            'SimpleIntegrationTest'       => $testName,
            '@group Plugins'              => '@group ' . $testType
         );

        $testClass  = $this->getTestClass($testType);
        if(!empty($testClass)) {
            $replace['\PHPUnit_Framework_TestCase'] = $testClass;

        }

        $whitelistFiles = $this->getTestFilesWhitelist($testType);
        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('Test %s for plugin %s generated.', $testName, $pluginName),
             'You can now start writing beautiful tests!',

        ));

        $this->writeSuccessMessage($output, array(
             'To run all your plugin tests, execute the command: ',
             sprintf('./console tests:run %s', $pluginName),
             'To run only this test: ',
             sprintf('./console tests:run %s', $testName),
             'Enjoy!'
        ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RunTimeException
     */
    private function getTestName(InputInterface $input, OutputInterface $output)
    {
        $testname = $input->getOption('testname');

        $validate = function ($testname) {
            if (empty($testname)) {
                throw new \InvalidArgumentException('You have to enter a valid test name ');
            }

            return $testname;
        };

        if (empty($testname)) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $testname = $dialog->askAndValidate($output, 'Enter the name of the test: ', $validate);
        } else {
            $validate($testname);
        }

        if (!Common::stringEndsWith(strtolower($testname), 'test')) {
            $testname = $testname . 'Test';
        }

        $testname = ucfirst($testname);

        return $testname;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RunTimeException
     */
    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNames();

        $validate = function ($pluginName) use ($pluginNames) {
            if (!in_array($pluginName, $pluginNames)) {
                throw new \InvalidArgumentException('You have to enter the name of an existing plugin');
            }

            return $pluginName;
        };

        $pluginName = $input->getOption('pluginname');

        if (empty($pluginName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $pluginName = $dialog->askAndValidate($output, 'Enter the name of your plugin: ', $validate, false, null, $pluginNames);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    private function getTestClass($testType)
    {
        if ('Database' == $testType) {
            return '\DatabaseTestCase';
        }
        if ('Unit' == $testType) {
            return '\PHPUnit_Framework_TestCase';
        }
        return false;
    }

    function getValidTypes()
    {
        return array('unit', 'integration', 'database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string Unit, Integration, Database
     */
    private function getTestType(InputInterface $input, OutputInterface $output)
    {
        $testtype = $input->getOption('testtype');

        $validate = function ($testtype) {


            if (empty($testtype) || !in_array($testtype, $this->getValidTypes())) {
                throw new \InvalidArgumentException('You have to enter a valid test type: ' . implode(" or ", $this->getValidTypes()));
            }
            return $testtype;
        };

        if (empty($testtype)) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $testtype = $dialog->askAndValidate($output, 'Enter the type of the test to generate: ', $validate);
        } else {
            $validate($testtype);
        }

        $testtype = ucfirst($testtype);
        return $testtype;
    }

    /**
     * @return array
     */
    protected function getTestFilesWhitelist($testType)
    {
        if('Integration' == $testType) {
            return array(
                '/tests',
                '/tests/SimpleIntegrationTest.php',
                '/tests/expected',
                '/tests/expected/test___API.get_day.xml',
                '/tests/expected/test___Goals.getItemsSku_day.xml',
                '/tests/processed',
                '/tests/processed/.gitignore',
                '/tests/fixtures',
                '/tests/fixtures/SimpleFixtureTrackFewVisits.php'
            );
        }
        return array(
            '/tests',
            '/tests/SimpleTest.php'
        );
    }
}