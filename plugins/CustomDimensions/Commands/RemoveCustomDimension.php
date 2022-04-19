<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Tracker\Cache;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->addOption('scope', null, InputOption::VALUE_REQUIRED, $description);
        $this->addOption('index', null, InputOption::VALUE_REQUIRED, 'Defines which specific Custom Dimension should be removed. To get a list of all available Custom Dimensions execute the command "./console customdimensions:info".');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scope = $this->getScope($input);

        $tracking = new LogTable($scope);
        $installedIndexes = $tracking->getInstalledIndexes();

        $index = $this->getIndex($input, $installedIndexes);

        $output->writeln(sprintf('Remove Custom Dimension at index %d in scope %s.', $index, $scope));

        $configuration = new Configuration();
        $configs = $configuration->getCustomDimensionsHavingIndex($scope, $index);

        $names = array();
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
        if (!$noInteraction && !$this->confirmChange($output)) {
            return;
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

        $this->writeSuccessMessage($output, array(
            sprintf('Your Matomo is now configured for up to %d Custom Dimensions in scope %s.', $numDimensionsAvailable, $scope)
        ));
    }

    private function getScope(InputInterface $input)
    {
        $scope = $input->getOption('scope');

        if (empty($scope) || !in_array($scope, array(CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION))) {
            $message = sprintf('The specified scope is invalid. Use either "--scope=%s" or "--scope=%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
            throw new \InvalidArgumentException($message);
        }

        return $scope;
    }

    private function getIndex(InputInterface $input, $installedIndexes)
    {
        $index = $input->getOption('index');

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

    private function confirmChange(OutputInterface $output)
    {
        $output->writeln('');

        $dialog = $this->getHelperSet()->get('dialog');
        return $dialog->askConfirmation(
            $output,
            '<question>Are you sure you want to perform this action? (y/N)</question>',
            false
        );
    }

}
