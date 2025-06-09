<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Tracker\Cache;

/**
 */
class RemoveCustomDimension extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('customdimensions:remove-custom-dimension');
        $this->setDescription('Removes an existing Custom Dimension');
        $this->setHelp("Example:
./console customdimensions:remove-custom-dimension --scope=action --index=4
=> Will remove the Custom Dimension having the index 4 in scope action.
");

        $description = sprintf('The scope of the Custom Dimension to remove, either "%s" or "%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
        $this->addRequiredValueOption('scope', null, $description);
        $this->addRequiredValueOption('index', null, 'Defines which specific Custom Dimension should be removed. To get a list of all available Custom Dimensions execute the command "./console customdimensions:info".');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $scope = $this->getScope();

        $tracking = new LogTable($scope);
        $installedIndexes = $tracking->getInstalledIndexes();

        $index = $this->getIndex($installedIndexes);

        $output->writeln(sprintf('Remove Custom Dimension at index %d in scope %s.', $index, $scope));

        $configuration = new Configuration();
        $configs = $configuration->getCustomDimensionsHavingIndex($scope, $index);

        $names = [];
        foreach ($configs as $config) {
            $names[] = $config['name'];
        }

        if (empty($names)) {
            $output->writeln('This index is currently not used by any website');
        } else {
            $output->writeln(sprintf('This index is used by %d websites and used for the following Custom Dimensions: "%s"', count($names), implode('", "', $names)));
        }

        $output->writeln('');
        $output->writeln('<comment>This causes schema changes in the database and may take a very long time.</comment>');
        $output->writeln('<comment>Removing tracked Custom Dimension data cannot be undone unless you have a backup.</comment>');

        $noInteraction = $input->getOption('no-interaction');
        if (!$noInteraction && !$this->confirmChange()) {
            return self::FAILURE;
        }

        $output->writeln('');
        $output->writeln('Starting to remove this Custom Dimension.');
        $output->writeln('');

        $tracking = new LogTable($scope);
        $tracking->removeCustomDimension($index);

        $configuration->deleteConfigurationsForIndex($index, $scope);

        if ($scope === CustomDimensions::SCOPE_VISIT) {
            $tracking = new LogTable(CustomDimensions::SCOPE_CONVERSION);
            $tracking->removeCustomDimension($index);
        }

        Cache::clearCacheGeneral();

        $numDimensionsAvailable = $tracking->getNumInstalledIndexes();

        $this->writeSuccessMessage(
            sprintf('Your Matomo is now configured for up to %d Custom Dimensions in scope %s.', $numDimensionsAvailable, $scope)
        );

        return self::SUCCESS;
    }

    private function getScope()
    {
        $scope = $this->getInput()->getOption('scope');

        if (empty($scope) || !in_array($scope, [CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION])) {
            $message = sprintf('The specified scope is invalid. Use either "--scope=%s" or "--scope=%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
            throw new \InvalidArgumentException($message);
        }

        return $scope;
    }

    private function getIndex($installedIndexes)
    {
        $index = $this->getInput()->getOption('index');

        $indexesHelp = 'Installed indexes are: ' . implode(', ', $installedIndexes);

        if (empty($index)) {
            throw new \InvalidArgumentException('An option "index" must be specified. ' . $indexesHelp);
        }

        if (!is_numeric($index)) {
            throw new \InvalidArgumentException('Option "index" must be a number');
        }

        $index = (int) $index;

        if (!in_array($index, $installedIndexes)) {
            throw new \InvalidArgumentException('Specified index is not installed. ' . $indexesHelp);
        }

        return $index;
    }

    private function confirmChange()
    {
        $this->getOutput()->writeln('');
        return $this->askForConfirmation(
            '<question>Are you sure you want to perform this action? (y/N)</question>',
            false
        );
    }
}
