<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Option;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Tracker\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DetectIfCdtEverUsed extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('coreadminhome:detect-if-cdt-ever-used');
        $this->addOption('sites', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The sites for which to detect if the cdt parameter was ever used. Can be individual site IDs or "all" to go through all of them.');
        $this->setDescription('Detects if the cdt parameter was ever used during tracking for one or more sites, resulting in visit data being out of order in the table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var RawLogDao $rawLogDao */
        $rawLogDao = StaticContainer::get(RawLogDao::class);

        $sites = $this->getSites($input);
        foreach ($sites as $idSite) {
            $output->writeln("Checking for whether site $idSite has out of order visit data...");

            list($hasLogDataOutOfOrder, $middleVisit, $outOfOrderVisit) = $rawLogDao->hasVisitDataOutOfOrder($idSite);

            $optionName = Request::HAS_USED_CDT_WHEN_TRACKING_OPTION_NAME_PREFIX . $idSite;
            if ($hasLogDataOutOfOrder) {
                $output->writeln("log_visit data is currently out of order for $idSite. Setting $optionName to 1.");
                $output->writeln('[middle visit ID = ' . $middleVisit . ', out of order visit = ' . $outOfOrderVisit . ']');
                Option::set($optionName, 1);
            } else {
                $output->writeln("log_visit is not out of order for $idSite. Setting $optionName to 0 to take advantage of optimizations.");
                Option::set($optionName, 0);
            }
        }
    }

    private function getSites(InputInterface $input)
    {
        $sitesArgument = $input->getOption('sites');

        foreach ($sitesArgument as $idSite) {
            if ($idSite == 'all') {
                $model = new Model();
                return $model->getSitesId();
            }
        }

        $sitesArgument = array_map('intval', $sitesArgument);
        $sitesArgument = array_map('trim', $sitesArgument);
        return $sitesArgument;
    }
}
