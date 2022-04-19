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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class Info extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('customdimensions:info');
        $this->setDescription('Get information about currently installed Custom Dimensions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (CustomDimensions::getScopes() as $scope) {
            $tracking = new LogTable($scope);
            $output->writeln(sprintf('%s Custom Dimensions available in scope "%s"', $tracking->getNumInstalledIndexes(), $scope));

            if ($scope === CustomDimensions::SCOPE_CONVERSION) {
                $output->writeln(sprintf('Custom Dimensions are automatically added via the scope "%s" and cannot be added manually', CustomDimensions::SCOPE_VISIT));
            } else {
                $output->writeln(sprintf('To add a Custom Dimension execute "<comment>./console customdimensions:add-custom-dimension --scope=%s</comment>"', $scope));
            }
            $output->writeln('Installed indexes are:');
            foreach ($tracking->getInstalledIndexes() as $index) {
                $output->writeln(sprintf('%d to remove this Custom Dimension execute <comment>./console customdimensions:remove-custom-dimension --scope=%s --index=%d</comment>', $index, $scope, $index));
            }
            $output->writeln('');
        }

        $visit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $numVisit = $visit->getNumInstalledIndexes();

        $conversion = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $numConversions = $conversion->getNumInstalledIndexes();

        if ($numConversions < $numVisit) {
            $output->writeln('');
            $output->writeln('<error>We found an error, Custom Dimensions in scope "conversion" are not correctly installed. Execute the following command to repair it:</error>');
            $output->writeln(sprintf('<comment>./console customdimensions:add-custom-dimension --scope=%s --count=%d</comment>', CustomDimensions::SCOPE_CONVERSION, $numVisit - $numConversions));
        }
    }

}
