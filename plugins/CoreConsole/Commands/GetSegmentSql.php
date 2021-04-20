<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\LogAggregator;
use Piwik\Development;
use Piwik\Metrics;
use Piwik\Period\Factory;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Segment;
use Piwik\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetSegmentSql extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:get-segment-sql');
        $this->setDescription('Print out the SQL used to query for a segment. Used for debugging or diagnosing segment issues. The site ID and dates are hardcoded in the query.');
        $this->addOption('segment', null, InputOption::VALUE_REQUIRED, 'The segment, correctly encoded.');
        $this->addOption('idSites', null, InputOption::VALUE_REQUIRED, 'Comma separated list of site IDs for the segment. (optional)');
        $this->addOption('queryType', null, InputOption::VALUE_REQUIRED, 'The query type to generate: visit, action or conversion');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $idSites = $input->getOption('idSites') ?: '';
        $idSites = explode(',', $idSites);
        $idSites = array_map('intval', $idSites);
        $idSites = array_filter($idSites);

        $queryType = $input->getOption('queryType');

        $segment = $input->getOption('segment');
        $segment = new Segment($segment, $idSites);

        $model = new Model();
        $allIdSites = $model->getSitesId();
        $idSiteInQuery = reset($allIdSites);

        $params = new Parameters(new Site($idSiteInQuery), Factory::build('day', 'today'), $segment);

        $logAggregator = new LogAggregator($params);
        switch ($queryType) {
            case 'visit':
                $query = $logAggregator->generateQuery(
                    implode(LogAggregator::FIELDS_SEPARATOR, [
                        Metrics::INDEX_NB_UNIQ_VISITORS               => "count(distinct " . LogAggregator::LOG_VISIT_TABLE . ".idvisitor)",
                        Metrics::INDEX_NB_VISITS                      => "count(*)",
                    ]),
                    [LogAggregator::LOG_VISIT_TABLE],
                    $logAggregator->getWhereStatement(LogAggregator::LOG_VISIT_TABLE, LogAggregator::VISIT_DATETIME_FIELD),
                    '',
                    ''
                );
                break;
            case 'action':
                $query = $logAggregator->generateQuery(
                    implode(LogAggregator::FIELDS_SEPARATOR, [
                        Metrics::INDEX_NB_VISITS        => "count(distinct " . LogAggregator::LOG_ACTIONS_TABLE . ".idvisit)",
                        Metrics::INDEX_NB_UNIQ_VISITORS => "count(distinct " . LogAggregator::LOG_ACTIONS_TABLE . ".idvisitor)",
                        Metrics::INDEX_NB_ACTIONS       => "count(*)",
                    ]),
                    [LogAggregator::LOG_ACTIONS_TABLE],
                    $logAggregator->getWhereStatement(LogAggregator::LOG_ACTIONS_TABLE, LogAggregator::ACTION_DATETIME_FIELD),
                    '',
                    ''
                );
                break;
            case 'conversion':
                $query = $logAggregator->generateQuery(
                    implode(LogAggregator::FIELDS_SEPARATOR, [
                        Metrics::INDEX_GOAL_NB_CONVERSIONS             => "count(*)",
                        Metrics::INDEX_GOAL_NB_VISITS_CONVERTED        => "count(distinct " . LogAggregator::LOG_CONVERSION_TABLE . ".idvisit)",
                    ]),
                    [LogAggregator::LOG_CONVERSION_TABLE],
                    $logAggregator->getWhereStatement(LogAggregator::LOG_CONVERSION_TABLE, LogAggregator::CONVERSION_DATETIME_FIELD),
                    '',
                    ''
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid value for --queryType, must be one of the following: visit, action, conversion');
        }

        $output->writeln("QUERY: " . $query['sql']);
        foreach ($query['bind'] as $key => $value) {
            $output->writeln('  BIND #' . $key . ': ' . $value);
        }
    }
}
