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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateTest extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:test')
            ->setDescription('Adds a test to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
            ->addOption('testname', null, InputOption::VALUE_REQUIRED, 'The name of the test to create')
            ->addOption('testtype', null, InputOption::VALUE_REQUIRED, 'Whether you want to create a "unit", "integration", "system", or "ui" test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $testType   = $this->getTestType($input, $output);
        $testName   = $this->getTestName($input, $output, $testType);

        $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
        $replace       = array(
            'ExamplePlugin'    => $pluginName,
            'SimpleTest'       => $testName,
            'SimpleSystemTest' => $testName,
            'SimpleUITest_spec.js' => $testName . '_spec.js',
            'SimpleUITest'     => $testName,
         );

        $whitelistFiles = $this->getTestFilesWhitelist($testType);
        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $messages = array(
            sprintf('Test %s for plugin %s generated.', $testName, $pluginName),
        );

        if (strtolower($testType) === 'ui') {
            $messages[] = 'To run this test execute the command: ';
            $messages[] = '<comment>' . sprintf('./console tests:run-ui %s', $testName) . '</comment>';
        } else {
            $messages[] = 'To run all your plugin tests, execute the command: ';
            $messages[] = '<comment>' . sprintf('./console tests:run %s', $pluginName) . '</comment>';
            $messages[] = 'To run only this test: ';
            $messages[] = '<comment>' . sprintf('./console tests:run %s', $testName) . '</comment>';
        }

        $messages[] = 'Enjoy!';

        $this->writeSuccessMessage($output, $messages);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RuntimeException
     */
    private function getTestName(InputInterface $input, OutputInterface $output, $testType)
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

        if (strtolower($testType) !== 'ui' && !Common::stringEndsWith(strtolower($testname), 'test')) {
            $testname = $testname . 'Test';
        }

        $testname = ucfirst($testname);

        return $testname;
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
        $invalidName = 'You have to enter the name of an existing plugin';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

    public function getValidTypes()
    {
        return array('unit', 'integration', 'system', 'ui');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string Unit, Integration, System
     */
    private function getTestType(InputInterface $input, OutputInterface $output)
    {
        $testtype = $input->getOption('testtype');

        $self = $this;

        $validate = function ($testtype) use ($self) {
            if (empty($testtype) || !in_array($testtype, $self->getValidTypes())) {
                throw new \InvalidArgumentException('You have to enter a valid test type: ' . implode(" or ", $self->getValidTypes()));
            }
            return $testtype;
        };

        if (empty($testtype)) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $testtype = $dialog->askAndValidate($output, 'Enter the type of the test to generate ('. implode(", ", $this->getValidTypes()).'): ', $validate, false, null, $this->getValidTypes());
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
        if ('Ui' == $testType) {
            return array(
                '/tests',
                '/tests/UI',
                '/tests/UI/.gitignore',
                '/tests/UI/expected-ui-screenshots',
                '/tests/UI/expected-ui-screenshots/.gitkeep',
                '/tests/UI/SimpleUITest_spec.js',
            );
        }

        if ('System' == $testType) {
            return array(
                '/.gitignore',
                '/tests',
                '/tests/System',
                '/tests/System/SimpleSystemTest.php',
                '/tests/System/expected',
                '/tests/System/expected/test___API.get_day.xml',
                '/tests/System/expected/test___Goals.getItemsSku_day.xml',
                '/tests/System/processed',
                '/tests/System/processed/.gitignore',
                '/tests/Fixtures',
                '/tests/Fixtures/SimpleFixtureTrackFewVisits.php'
            );
        }

        if ('Integration' == $testType) {

            return array(
                '/tests',
                '/tests/Integration',
                '/tests/Integration/SimpleTest.php'
            );
        }

        return array(
            '/tests',
            '/tests/Unit',
            '/tests/Unit/SimpleTest.php'
        );
    }
}
