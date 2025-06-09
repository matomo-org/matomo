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
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Tracker\Cache;

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
        $this->addRequiredValueOption('scope', null, $description);
        $this->addRequiredValueOption('count', null, 'Define how many Custom Dimensions shall be added', '1');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $scope = $this->getScope();
        $count = $this->getCount();

        $output->writeln(sprintf('Adding %d Custom Dimension(s) in scope %s.', $count, $scope));
        $output->writeln('<info>This causes schema changes in the database and may take a very long time.</info>');

        $noInteraction = $input->getOption('no-interaction');
        if (!$noInteraction && !$this->confirmChange()) {
            return self::FAILURE;
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

        $this->writeSuccessMessage(
            sprintf('Your Matomo is now configured for up to %d Custom Dimensions in scope %s.', $numDimensionsAvailable, $scope)
        );

        return self::SUCCESS;
    }

    private function getScope()
    {
        $scope = $this->getInput()->getOption('scope');

        if (empty($scope) || !in_array($scope, CustomDimensions::getScopes())) {
            // we also allow scope "conversion" in case on needs to repair something but we don't document as it would be rather confusing
            $message = sprintf('The specified scope is invalid. Use either "--scope=%s" or "--scope=%s"', CustomDimensions::SCOPE_VISIT, CustomDimensions::SCOPE_ACTION);
            throw new \InvalidArgumentException($message);
        }

        return $scope;
    }

    private function getCount()
    {
        $count = $this->getInput()->getOption('count');

        if (!is_numeric($count)) {
            throw new \InvalidArgumentException('Option "count" must be a number');
        }

        $count = (int) $count;

        if ($count <= 0) {
            throw new \InvalidArgumentException('Option "count" must be at least one');
        }

        return $count;
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
