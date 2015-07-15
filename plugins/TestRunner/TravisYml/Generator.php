<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\TravisYml;

use Exception;
use Piwik\Plugins\TestRunner\Commands\GenerateTravisYmlFile;
use Psr\Log\LoggerInterface;

/**
 * Base class for .travis.yml file generators.
 */
abstract class Generator
{
    /**
     * @var string[]
     */
    protected $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TravisYmlView
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param string[] $options The string options applied to the generate:travis-yml command.
     */
    public function __construct($options)
    {
        $this->options = $options;
        if (class_exists('\Piwik\Container\StaticContainer')) {
            $this->logger = \Piwik\Container\StaticContainer::getContainer()->get('Psr\Log\LoggerInterface');
        }

        $this->view = new TravisYmlView();
    }

    /**
     * Generates the contents of a .travis.yml file and returns them.
     *
     * @return string
     */
    public function generate()
    {
        $this->configureView();

        return $this->view->render();
    }

    /**
     * Writes the contents of a .travis.yml file to the correct destination. If the --dump option
     * is specified, the file is saved here instead of the .travis.yml file it should be saved to.
     *
     * @param string $travisYmlContents
     * @return string Returns the path of the file that was written to.
     * @throws Exception if the path being written is not writable.
     */
    public function dumpTravisYmlContents($travisYmlContents)
    {
        $writePath = @$this->options['dump'];
        if (empty($writePath)) {
            $writePath = $this->getTravisYmlOutputPath();
        }

        if (!is_writable(dirname($writePath))) {
            throw new Exception("Cannot write to '$writePath'!");
        }

        file_put_contents($writePath, $travisYmlContents);

        return $writePath;
    }

    /**
     * Returns the path of the .travis.yml file we are generating. The --dump option has no effect on
     * this path.
     */
    public abstract function getTravisYmlOutputPath();

    protected function configureView()
    {
        $thisConsoleCommand = $this->getExecutedConsoleCommandForTravis();
        $this->view->setGenerateYmlCommand($thisConsoleCommand);

        $phpVersions = @$this->options['php-versions'];
        if (!empty($phpVersions)) {
            $this->view->setPhpVersions(explode(',', $phpVersions));
        }

        $outputYmlPath = $this->getTravisYmlOutputPath();
        if (file_exists($outputYmlPath)) {
            $this->log('info', "Found existing YAML file at {path}.", array('path' => $outputYmlPath));

            $parser = new Parser();
            $existingSections = $parser->processExistingTravisYml($outputYmlPath);
            $this->view->setExistingSections($existingSections);
        } else {
            $this->log('info', "Could not find existing YAML file at {path}, generating a new one.", array('path' => $outputYmlPath));
        }

        $this->setExtraEnvironmentVariables();
    }

    protected function travisEncrypt($data)
    {
        $this->log('info', "Encrypting \"{data}\"...", array('data' => $data));

        $command = "travis encrypt \"$data\"";

        exec($command, $commandOutput, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception("Cannot encrypt \"$data\" for travis! Please make sure you have the travis command line "
                . "utility installed (see http://blog.travis-ci.com/2013-01-14-new-client/).\n\n"
                . "return code: $returnCode\n\n"
                . "travis output:\n\n" . implode("\n", $commandOutput));
        }

        if (empty($commandOutput)) {
            throw new Exception("Cannot parse travis encrypt output:\n\n" . implode("\n", $commandOutput));
        }

        // when not executed from a command line travis encrypt will return only the encrypted data
        $encryptedData = $commandOutput[0];

        if (substr($encryptedData, 0, 1) != '"'
            || substr($encryptedData, -1) != '"'
        ) {
            $encryptedData = '"' . addslashes($encryptedData) . '"';
        }

        return "secure: " . $encryptedData;
    }

    protected function getExecutedConsoleCommandForTravis()
    {
        $command = "php ./console " . GenerateTravisYmlFile::COMMAND_NAME;

        $options = $this->getOptionsForSelfReferentialCommand();

        foreach ($options as $name => $value) {
            if ($value === false
                || $value === null
            ) {
                continue;
            }

            if ($value === true) {
                $command .= " --$name";
            } else if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $command .= " --$name=\"" . addslashes($arrayValue) . "\"";
                }
            } else {
                $command .= " --$name=\"" . addslashes($value) . "\"";
            }
        }

        return $command;
    }

    private function setExtraEnvironmentVariables()
    {
        if (!empty($this->view->existingEnv)) {
            $this->log('info', "Existing .yml file found, ignoring global variables specified on command line.");
            return;
        }

        $extraVars = array();

        $artifactsPass = @$this->options['artifacts-pass'];
        if (!empty($artifactsPass)) {
            $extraVars[] = $this->travisEncrypt("ARTIFACTS_PASS=" . $artifactsPass);
        }

        $githubToken = @$this->options['github-token'];
        if (!empty($githubToken)) {
            $extraVars[] = $this->travisEncrypt("GITHUB_USER_TOKEN=" . $githubToken);
        }

        $this->view->setExtraGlobalEnvVars($extraVars);
    }

    protected function getOptionsForSelfReferentialCommand()
    {
        $options = $this->options;
        unset($options['github-token']);
        unset($options['artifacts-pass']);
        unset($options['dump']);
        $options['verbose'] = true;
        return $options;
    }

    protected function log($level, $message, $params = array())
    {
        if ($this->logger) {
            $this->logger->$level($message, $params);
        }
    }
}