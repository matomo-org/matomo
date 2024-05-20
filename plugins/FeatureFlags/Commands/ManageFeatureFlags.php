<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;

class ManageFeatureFlags extends ConsoleCommand
{
    private const ACTION_OPTIONS = [
        'enable',
        'disable',
        'delete'
    ];

    /**
     * This method allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {
        $this->setName('featureflags:manage');
        $this->setDescription('Manage feature flags (enable/disable/delete)');
        $this->addRequiredValueOption('name', null, 'Feature flag name');
        $this->addRequiredValueOption('action', null, 'enable/disable/delete');
    }

    /**
     * The actual task is defined in this method. Here you can access any option or argument that was defined on the
     * command line via $this->getInput() and write anything to the console via $this->getOutput().
     * In case anything went wrong during the execution you should throw an exception to make sure the user will get a
     * useful error message and to make sure the command does not exit with the status code 0.
     *
     * Ideally, the actual command is quite short as it acts like a controller. It should only receive the input values,
     * execute the task by calling a method of another class and output any useful information.
     *
     * Execute the command like: ./console examplecommand:helloworld --name="The Matomo Team"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $name = $input->getOption('name');
        $action = $input->getOption('action');

        if (!in_array($action, self::ACTION_OPTIONS)) {
            throw new \Exception("Action must be 'enable', 'disable' or 'delete'");
        }

        $featureFlag = $this->loadFeatureFlag($name);

        if ($featureFlag === null) {
            throw new \Exception("Feature flag could not be found");
        }

        /** @var FeatureFlagStorageInterface $storage */
        foreach (StaticContainer::get('featureflag.storages') as $storage) {
            switch ($action) {
                case self::ACTION_OPTIONS[0]: // Enable
                    $storage->enableFeatureFlag($featureFlag);
                case self::ACTION_OPTIONS[1]: // Disable
                    $storage->disableFeatureFlag($featureFlag);
                case self::ACTION_OPTIONS[2]: // Delete
                    $storage->deleteFeatureFlag($featureFlag);
            }
        }

        return self::SUCCESS;
    }

    private function loadFeatureFlag(string $name): ?FeatureFlagInterface
    {
        $classes = StaticContainer::get('featureflag.feature_flags');
        foreach ($classes as $featureFlagClass) {
            if (!is_subclass_of($featureFlagClass, FeatureFlagInterface::class)) {
                continue;
            }
            if ((new $featureFlagClass)->getName() === $name) {
                return new $featureFlagClass();
            }
        }

        return null;
    }
}
