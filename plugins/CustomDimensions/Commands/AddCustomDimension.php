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
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Tracker\Cache;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class AddCustomDimension extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('customdimensions:add-custom-dimension');
        $this->setDescription('Add new Custom Dimension available.');
        $this->setHelp("Example:
./console customdimensions:add-custom-dimension --scope=action --count=10
=> Will add 10 new Custom Dimensions in scope 'action'.
");

        $description = sprintf('The scope of the Custom Dimension to add, either "%s" or "%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
        $this->addOption('scope', null, InputOption::VALUE_REQUIRED, $description);
        $this->addOption('count', null, InputOption::VALUE_REQUIRED, 'Define how many Custom Dimensions shall be added', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scope = $this->getScope($input);
        $count = $this->getCount($input);

        $output->writeln(sprintf('Adding %d Custom Dimension(s) in scope %s.', $count, $scope));
        $output->writeln('<info>This causes schema changes in the database and may take a very long time.</info>');

        $noInteraction = $input->getOption('no-interaction');
        if (!$noInteraction && !$this->confirmChange($output)) {
            return;
        }

        $output->writeln('');
        $output->writeln('Starting to add Custom Dimension(s)');
        $output->writeln('');

        $tracking = new LogTable($scope);
        $tracking->addManyCustomDimensions($count);

        if ($scope === CustomDimensions::SCOPE_VISIT) {
            $tracking = new LogTable(CustomDimensions::SCOPE_CONVERSION);
            $tracking->addManyCustomDimensions($count);
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

        if (empty($scope) || !in_array($scope, CustomDimensions::getScopes())) {
            // we also allow scope "conversion" in case on needs to repair something but we don't document as it would be rather confusing
            $message = sprintf('The specified scope is invalid. Use either "--scope=%s" or "--scope=%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
            throw new \InvalidArgumentException($message);
        }

        return $scope;
    }

    private function getCount(InputInterface $input)
    {
        $count = $input->getOption('count');

        if (!is_numeric($count)) {
            throw new \InvalidArgumentException('Option "count" must be a number');
        }

        $count = (int) $count;

        if ($count <= 0) {
            throw new \InvalidArgumentException('Option "count" must be at least one');
        }

        return $count;
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
