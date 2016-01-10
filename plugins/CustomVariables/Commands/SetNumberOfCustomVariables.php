<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomVariables\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CustomVariables\Model;
use Piwik\Tracker\Cache;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class SetNumberOfCustomVariables extends ConsoleCommand
{
    /**
     * @var \Symfony\Component\Console\Helper\ProgressHelper
     */
    private $progress;

    protected function configure()
    {
        $this->setName('customvariables:set-max-custom-variables');
        $this->setDescription('Change the number of available custom variables');
        $this->setHelp("Example:
./console customvariables:set-max-custom-variables 10
=> 10 custom variables will be available in total
");
        $this->addArgument('maxCustomVars', InputArgument::REQUIRED, 'Set the number of max available custom variables');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numVarsToSet = $this->getNumVariablesToSet($input);
        $numChangesToPerform = $this->getNumberOfChangesToPerform($numVarsToSet);

        if (0 === $numChangesToPerform) {
            $this->writeSuccessMessage($output, array(
                'Your Piwik is already configured for ' . $numVarsToSet . ' custom variables.'
            ));
            return;
        }

        $output->writeln('');
        $output->writeln(sprintf('Configuring Piwik for %d custom variables', $numVarsToSet));

        foreach (Model::getScopes() as $scope) {
            $this->printChanges($scope, $numVarsToSet, $output);
        }

        if ($input->isInteractive() && !$this->confirmChange($output)) {
            return;
        }

        $output->writeln('');
        $output->writeln('Starting to apply changes');
        $output->writeln('');

        $this->progress = $this->initProgress($numChangesToPerform, $output);

        foreach (Model::getScopes() as $scope) {
            $this->performChange($scope, $numVarsToSet, $output);
        }

        Cache::clearCacheGeneral();
        $this->progress->finish();

        $this->writeSuccessMessage($output, array(
            'Your Piwik is now configured for ' . $numVarsToSet . ' custom variables.'
        ));
    }

    private function initProgress($numChangesToPerform, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $numChangesToPerform);

        return $progress;
    }

    private function performChange($scope, $numVarsToSet, OutputInterface $output)
    {
        $model = new Model($scope);
        $numCurrentVars = $model->getCurrentNumCustomVars();
        $numDifference  = $this->getAbsoluteDifference($numCurrentVars, $numVarsToSet);

        if ($numVarsToSet > $numCurrentVars) {
            $this->addCustomVariables($model, $numDifference, $output);
            return;
        }

        $this->removeCustomVariables($model, $numDifference, $output);
    }

    private function getNumVariablesToSet(InputInterface $input)
    {
        $maxCustomVars = $input->getArgument('maxCustomVars');

        if (!is_numeric($maxCustomVars)) {
            throw new \InvalidArgumentException('The number of available custom variables has to be a number');
        }

        $maxCustomVars = (int) $maxCustomVars;

        if ($maxCustomVars < 5) {
            throw new \InvalidArgumentException('There has to be at least five custom variables');
        }

        return $maxCustomVars;
    }

    private function confirmChange(OutputInterface $output)
    {
        $output->writeln('');

        $dialog = $this->getHelperSet()->get('dialog');
        return $dialog->askConfirmation(
            $output,
            '<question>Are you sure you want to perform these actions? (y/N)</question>',
            false
        );
    }

    private function printChanges($scope, $numVarsToSet, OutputInterface $output)
    {
        $model                = new Model($scope);
        $scopeName            = $model->getScopeName();
        $highestIndex         = $model->getHighestCustomVarIndex();
        $numCurrentCustomVars = $model->getCurrentNumCustomVars();
        $numVarsDifference    = $this->getAbsoluteDifference($numCurrentCustomVars, $numVarsToSet);

        $output->writeln('');
        $output->writeln(sprintf('Scope "%s"', $scopeName));

        if ($numVarsToSet > $numCurrentCustomVars) {

            $indexes = $highestIndex + 1;
            if (1 !== $numVarsDifference) {
                $indexes .= ' - ' . ($highestIndex + $numVarsDifference);
            }

            $output->writeln(
                sprintf('%s new custom variables having the index(es) %s will be ADDED', $numVarsDifference, $indexes)
            );

        } elseif ($numVarsToSet < $numCurrentCustomVars) {

            $indexes = $highestIndex - $numVarsDifference + 1;

            if (1 !== $numVarsDifference) {
                $indexes .= ' - ' . $highestIndex;
            }

            $output->writeln(
                sprintf("%s existing custom variables having the index(es) %s will be REMOVED.", $numVarsDifference, $indexes)
            );
            $output->writeln('<comment>This is an irreversible change</comment>');
        }
    }

    private function getAbsoluteDifference($currentNumber, $numberToSet)
    {
        return abs($numberToSet - $currentNumber);
    }

    private function removeCustomVariables(Model $model, $numberOfVarsToRemove, OutputInterface $output)
    {
        for ($index = 0; $index < $numberOfVarsToRemove; $index++) {
            $indexRemoved = $model->removeCustomVariable();
            $this->progress->advance();
            $output->writeln('  <info>Removed a variable in scope "' . $model->getScopeName() .  '" having the index ' . $indexRemoved . '</info>');
        }
    }

    private function addCustomVariables(Model $model, $numberOfVarsToAdd, OutputInterface $output)
    {
        for ($index = 0; $index < $numberOfVarsToAdd; $index++) {
            $indexAdded = $model->addCustomVariable();
            $this->progress->advance();
            $output->writeln('  <info>Added a variable in scope "' . $model->getScopeName() .  '" having the index ' . $indexAdded . '</info>');
        }
    }

    private function getNumberOfChangesToPerform($numVarsToSet)
    {
        $numChangesToPerform = 0;

        foreach (Model::getScopes() as $scope) {
            $model = new Model($scope);
            $numCurrentCustomVars = $model->getCurrentNumCustomVars();
            $numChangesToPerform += $this->getAbsoluteDifference($numCurrentCustomVars, $numVarsToSet);
        }

        return $numChangesToPerform;
    }
}
