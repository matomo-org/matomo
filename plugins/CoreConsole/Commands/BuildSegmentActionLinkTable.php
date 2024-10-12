<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use DateInterval;
use DatePeriod;
use DateTime;
use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * This is a proof of concept console command to add segment part link table definitions and backfill data for existing
 * segments and actions in the current dataset.
 *
 * It's intended to quickly allow performance testing to validate the segment part action link table approach. It's not
 * meant to be production code at any point.
 */
class BuildSegmentActionLinkTable extends ConsoleCommand
{
    private $verbose = false;
    private $noSites = false;
    private $dateFrom = null;
    private $dateTo = null;

    protected function configure()
    {
        $this->setName('development:build-salt');
        $this->setDescription('Test');
        $this->addRequiredValueOption('DateFrom', null, '', '2022-01-01');
        $this->addRequiredValueOption('DateTo', null, '', '2023-01-01');
        $this->addNoValueOption('v', null, 'Show more verbose debug info');
        $this->addNoValueOption('nosites', null, 'Prevent use of idsite in link tables');
        $this->addNoValueOption('drop', null, 'Drop the segment part action link tables');
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $this->verbose = $input->getOption('v');
        $this->noSites = $input->getOption('nosites');

        $this->dateFrom = DateTime::createFromFormat('Y-m-d', $input->getOption('DateFrom'));
        $this->dateTo = DateTime::createFromFormat('Y-m-d', $input->getOption('DateTo'));

        $output = $this->getOutput();
        if ($dateFrom === false || $dateTo === false) {
            $output->write("Error: DateFrom / DateTo are required and should be in the format YYYY-MM-DD");
            return 0;
        }

        if ($input->getOption('drop')) {
            $this->drop();
            return 1;
        }

        $output->write("Creating link tables...");

        Db::exec('
        CREATE TABLE IF NOT EXISTS ' . Common::prefixTable('log_link_segment_part_action') . '
        (
          idaction               bigint(10) UNSIGNED,
          idsegmentpart          bigint(10) UNSIGNED,
          PRIMARY KEY (idaction, idsegmentpart)
        );
        ');

        Db::exec('
        CREATE TABLE IF NOT EXISTS ' . Common::prefixTable('log_link_segment_part') . '
        (
          idsegmentpart          bigint(10) UNSIGNED AUTO_INCREMENT,
          idsite                 bigint(10) UNSIGNED,
          segmentpart            varchar(100),
          last_idaction_checked  bigint(10) UNSIGNED,
          PRIMARY KEY (idsegmentpart)
        );
        ');
        $output->writeln("done.");

        $segments = Db::query("SELECT * FROM segment WHERE deleted = 0");
        $output->write("Populating link tables data for " . $segments->rowCount() . " segments...");
        foreach ($segments as $segRow) {
            $sites = [];
            if ($segRow['enable_only_idsite']) {
                $sites[] = $segRow['enable_only_idsite'];
            }
            $s = new Segment($segRow['definition'], $sites);

            // $output->writeln(json_encode($s->parsedSubExpressions));

            $se = $s->getSegmentExpression();
            $subExprs = $se->parseSubExpressions();
            foreach ($subExprs as $ses) {
                foreach ($ses as $ss) {
                    // convert segments name to sql segment
                    // check that user is allowed to view this segment
                    // and apply a filter to the value to match if necessary (to map DB fields format)
                    $this->processPart($ss[0], $ss[1], $ss[2], ($this->noSites ? null : $segRow['enable_only_idsite']));
                }
            }
            $output->write(".");
        }
        $output->writeln("done.");
        return 1;
    }

    /**
     * Populate the segment link tables for a specifig segment part
     *
     * @param string   $col
     * @param string   $operand
     * @param string   $value
     * @param int|null $idsite
     *
     * @return void
     * @throws \Exception
     */
    private function processPart(string $col, string $operand, string $value, ?int $idsite = null)
    {
        $part = $col . $operand . $value;
        $output = $this->getOutput();

        // Create log_link_segment_part record if not existing and/or get id
        $idSegmentPart = Db::fetchOne('SELECT idsegmentpart FROM ' . Common::prefixTable('log_link_segment_part') . ' WHERE segmentpart = ?', [$part]);
        if (!$idSegmentPart) {
            Db::query('INSERT INTO ' . Common::prefixTable('log_link_segment_part') . ' (segmentpart, last_idaction_checked, idsite) VALUES (?, 0, ?)',
                [$part, $idsite]);
            $idSegmentPart = Db::fetchOne("SELECT LAST_INSERT_ID()");
        }
        if ($this->verbose) {
            $output->writeln(">>>>   " . $part . ", idsegmentpart: " . $idSegmentPart);
        }

        // Match actions and populate log_link_segment_part_action

        // This is a hack to support enough segment expressions for testing the PoC.
        // Eventually it should be done properly using updated methods on the Segment class to support all action operations.
        $query = null;

        if ($col == 'actionType') {
            $query = TableLogAction::getIdActionFromSegment(self::findActionType($value), '', $operand, $col);
            $query['SQL'] = trim($query['SQL'], ' )');
            $query['SQL'] = str_replace('SELECT idaction', 'SELECT idaction, ' . $idSegmentPart, $query['SQL']);
        }
        if ($col == 'actionUrl' || $col == 'pageUrl') {
            $idaction = TableLogAction::getIdActionFromSegment($value, '', $operand, 'pageurl');
            if (is_array($idaction)) {
                $query = $idaction;
                $query['SQL'] = str_replace('SELECT idaction', 'SELECT idaction, ' . $idSegmentPart, $query['SQL']);
            } else if (is_numeric($idaction) && $idaction > 0) {
                $query = ['SQL' => "SELECT idaction, " . $idSegmentPart . " FROM " . Common::prefixTable('log_action') . " WHERE idaction = ?", 'bind' => [$idaction]];
            }
        }

        if ($query) {

            // If sites are to be used then make sure only actions which have visits for the segment site are matched
            if (!$this->noSites && $idsite !== null) {

                // Group by action id and add a clause to only include actions for visits for the chosen site
                $query['SQL'] .= ' AND log_action.idaction IN (
                    SELECT llva_u.#columnNameToJoin#
                        FROM log_link_visit_action llva_u
                        WHERE
                            llva_u.idsite = ?
                         AND (llva_u.server_time > ?
                            AND llva_u.server_time < ?)
                )';

                // Add binds for the site
                if (!is_array($query['bind'])) {
                    $query['bind'] = [$query['bind']];
                }
                $query['bind'][] = $idsite;
            }

            // Use an INSERT INTO ... SELECT for speed
            $insertSQL = 'INSERT IGNORE INTO ' . Common::prefixTable('log_link_segment_part_action') . ' ' . $query['SQL'];

            $startDate = $this->dateFrom;
            $endDate = $this->dateTo;

            $interval = new DateInterval('P1D'); // 1 Day interval
            $period = new DatePeriod($startDate, $interval, $endDate);

            foreach ($period as $date) {

                $startDate = $date->format('Y-m-d 00:00:00');
                $endDate = $date->format('Y-m-d 23:59:59');

                $output->writeln("Starting date range: $startDate, End: $endDate");

                $params = array_merge($query['bind'], [$startDate, $endDate]);

                $output->writeln($insertSQL);
                $output->writeln(json_encode($params));


                Db::query(str_replace('#columnNameToJoin#', 'idaction_name', $insertSQL), $params);
                Db::query(str_replace('#columnNameToJoin#', 'idaction_url', $insertSQL), $params);

                $output->writeln((new DateTime())->format('H:i:s') . " Finished range: $startDate, End: $endDate");
            }
        }
        // Set the max idactions matched for each part (not really needed for query testing)
        //$maxIdAction = Db::fetchOne('SELECT MAX(idaction) FROM ' . Common::prefixTable('log_link_segment_part_action') . ' WHERE idsegmentpart = ?', [$idSegmentPart]);
        //Db::query('UPDATE ' . Common::prefixTable('log_link_segment_part') . ' SET last_idaction_checked = ? WHERE idsegmentpart = ?',
        // [$maxIdAction, $idSegmentPart]);
    }

    private static function findActionType($segmentPart)
    {
        switch ($segmentPart) {
            case 'pageviews':
                return Action::TYPE_PAGE_URL;
            case 'contents':
                return Action::TYPE_CONTENT;
            case 'sitesearches':
                return Action::TYPE_SITE_SEARCH;
            case 'events':
                return Action::TYPE_EVENT;
            case 'outlinks':
                return Action::TYPE_OUTLINK;
            case 'downloads':
                return Action::TYPE_DOWNLOAD;
        }
        return null;
    }

    private function drop()
    {
        $output = $this->getOutput();
        $output->write('Dropping link tables...');
        Db::query('DROP TABLE IF EXISTS log_link_segment_part');
        Db::query('DROP TABLE IF EXISTS log_link_segment_part_action');
        $output->writeln('done');
    }
}
