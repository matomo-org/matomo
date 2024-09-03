<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;

/**
 * Diagnostic command that analyzes a single archive table. Displays information like # of segment archives,
 * # invalidated archives, # temporary archives, etc.
 */
class AnalyzeArchiveTable extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:analyze-archive-table');
        $this->setDescription('Analyze an archive table and display human readable information about what is stored. '
            . 'This command can be used to diagnose issues like bloated archive tables.');
        $this->addRequiredArgument('table-date', "The table's associated date, eg, 2015_01 or 2015_02");
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $tableDate = $input->getArgument('table-date');

        $output->writeln("<comment>Statistics for the archive_numeric_$tableDate and archive_blob_$tableDate tables:</comment>");
        $output->writeln("");

        $archiveTableDao = StaticContainer::get('Piwik\DataAccess\ArchiveTableDao');
        $rows = $archiveTableDao->getArchiveTableAnalysis($tableDate);

        // process labels
        $periodIdsToLabels = array_flip(Piwik::$idPeriods);
        foreach ($rows as $key => &$row) {
            list($idSite, $date1, $date2, $period) = explode('.', $key);

            $periodLabel = isset($periodIdsToLabels[$period]) ? $periodIdsToLabels[$period] : "Unknown Period ($period)";
            $row['label'] = $periodLabel . "[" . $date1 . " - " . $date2 . "] idSite = " . $idSite;
        }

        $headers = array('Group', '# Archives', '# Invalidated', '# Temporary', '# Error', '# Segment',
            '# Numeric Rows', '# Blob Rows', '# Blob Data');

        // display all rows
        $this->renderTable($headers, $rows);

        // display summary
        $totalArchives = 0;
        $totalInvalidated = 0;
        $totalTemporary = 0;
        $totalError = 0;
        $totalSegment = 0;
        $totalBlobLength = 0;

        foreach ($rows as $row) {
            $totalArchives += (int) $row['count_archives'];
            $totalInvalidated += (int) $row['count_invalidated_archives'];
            $totalTemporary += (int) $row['count_temporary_archives'];
            $totalError += (int) $row['count_error_archives'];
            $totalSegment += (int) $row['count_segment_archives'];

            if (isset($row['sum_blob_length'])) {
                $totalBlobLength += (int) $row['sum_blob_length'];
            }
        }

        $formatter = new Formatter();

        $output->writeln("");
        $output->writeln("Total # Archives: <comment>$totalArchives</comment>");
        $output->writeln("Total # Invalidated Archives: <comment>$totalInvalidated</comment>");
        $output->writeln("Total # Temporary Archives: <comment>$totalTemporary</comment>");
        $output->writeln("Total # Error Archives: <comment>$totalError</comment>");
        $output->writeln("Total # Segment Archives: <comment>$totalSegment</comment>");
        $output->writeln("Total Size of Blobs: <comment>" . $formatter->getPrettySizeFromBytes($totalBlobLength) . "</comment>");
        $output->writeln("");

        return self::SUCCESS;
    }
}
