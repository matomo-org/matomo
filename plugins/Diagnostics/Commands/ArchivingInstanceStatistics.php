<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Common;
use Piwik\Date;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Db;

/**
 * Diagnostic command that returns instance statistics related to archiving
 */
class ArchivingInstanceStatistics extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:archiving-instance-statistics');
        $this->addNoValueOption(
            'json',
            null,
            "If supplied, the command will return data in json format"
        );
        $this->setDescription('Show data statistics which can affect archiving performance');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $metrics = $this->getArchivingInstanceStatistics();

        if ($input->getOption('json')) {
            $output->write(json_encode($metrics));
        } else {
            $headers = ['Statistic Name', 'Value'];
            $this->renderTable($headers, $metrics);
        }

        return self::SUCCESS;
    }

    /**
     * Retrieve various data statistics useful for diagnosing archiving performance
     *
     * @return array
     */
    public function getArchivingInstanceStatistics(): array
    {
        $stats = [];
        $stats[] = ['Site Count', (int) Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable("site"))];
        $stats[] = ['Segment Count', (int) Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable("segment"))];
        $stats[] = ['Database Version', defined('PIWIK_TEST_MODE') ? 'mysql-version-redacted' : Db::get()->getServerVersion()];
        $stats[] = ['Last full Month Hits', (int) Db::fetchOne(
            "SELECT COUNT(*) FROM " . Common::prefixTable("log_link_visit_action") . " WHERE server_time >= ? AND server_time <= ?",
            [
                Date::now()->setDay(1)->subMonth(1)->setTime('00:00:00')->toString('Y-m-d H:i:s'),
                Date::now()->setDay(1)->subDay(1)->setTime('23:59:59')->toString('Y-m-d H:i:s')
            ]
        )
        ];
        $stats[] = ['Last 12 Month Hits', (int) Db::fetchOne(
            "SELECT COUNT(*) FROM " . Common::prefixTable("log_link_visit_action") . " WHERE server_time >= ? AND server_time <= ?",
            [
                Date::now()->setDay(1)->subMonth(12)->setTime('00:00:00')->toString('Y-m-d H:i:s'),
                Date::now()->setDay(1)->subDay(1)->setTime('23:59:59')->toString('Y-m-d H:i:s')
            ]
        )
        ];
        return $stats;
    }
}
