<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;

class AnonymizeRawData extends ConsoleCommand
{
    protected function configure()
    {
        // by default we want to anonymize as many (if not all) logs as possible.
        $defaultDate = '1996-01-01,' . Date::now()->toString();

        $this->setName('privacymanager:anonymize-some-raw-data');
        $this->setDescription(
            'Anonymize some of the stored raw data (logs). The reason it only anonymizes "some" data is that ' .
            'personal data can be present in many various data collection points, for example some of your page URLs ' .
            'or page titles may include personal data and these will not be anonymized by this command as it is not ' .
            'possible to detect personal data for example in a URL automatically.'
        );
        $this->addRequiredValueOption('date', null, 'Date or date range to invalidate raw data for (UTC). Either a date like "2015-01-03" or a range like "2015-01-05,2015-02-12". By default, all data including today will be anonymized.', $defaultDate);
        $this->addRequiredValueOption('unset-visit-columns', null, 'Comma separated list of log_visit columns that you want to unset. Each value for that column will be set to its default value. If the same column exists in "log_conversion" table as well, the column will be unset there as well. This action cannot be undone.', '');
        $this->addRequiredValueOption('unset-link-visit-action-columns', null, 'Comma separated list of log_link_visit_action columns that you want to unset. Each value for that column will be set to its default value. This action cannot be undone.', '');
        $this->addNoValueOption('anonymize-ip', null, 'If set, the IP will be anonymized with a mask of at least 2. This action cannot be undone.');
        $this->addNoValueOption('anonymize-location', null, 'If set, the location will be re-evaluated based on the anonymized IP. This action cannot be undone.');
        $this->addNoValueOption('anonymize-userid', null, 'If set, any set user-id will be anonymized. This action cannot be undone.');
        $this->addRequiredValueOption('idsites', null, 'By default, the data of all idSites will be anonymized or unset. However, you can specify a set of idSites to execute this command only on these idsites.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $date = $input->getOption('date');
        $visitColumnsToUnset = $input->getOption('unset-visit-columns');
        if (!empty($visitColumnsToUnset)) {
            $visitColumnsToUnset = explode(',', $visitColumnsToUnset);
        }
        $linkVisitActionColumns = $input->getOption('unset-link-visit-action-columns');
        if (!empty($linkVisitActionColumns)) {
            $linkVisitActionColumns = explode(',', $linkVisitActionColumns);
        }

        $idSites = $input->getOption('idsites');
        if (!empty($idSites)) {
            $idSites = Site::getIdSitesFromIdSitesString($idSites);
        } else {
            $idSites = null;
        }
        $anonymizeIp = $input->getOption('anonymize-ip');
        $anonymizeLocation = $input->getOption('anonymize-location');
        $anonymizeUserId = $input->getOption('anonymize-userid');

        $logDataAnonymizations = StaticContainer::get('Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations');

        [$startDate, $endDate] = $logDataAnonymizations->getStartAndEndDate($date);
        $output->writeln(sprintf('Start date is "%s", end date is "%s"', $startDate, $endDate));

        if (
            $anonymizeIp
            && !$this->confirmAnonymize($startDate, $endDate, 'anonymize visit IP')
        ) {
            $anonymizeIp = false;
            $output->writeln('<info>SKIPPING anonymizing IP.</info>');
        }

        if (
            $anonymizeLocation
            && !$this->confirmAnonymize($startDate, $endDate, 'anonymize visit location')
        ) {
            $anonymizeLocation = false;
            $output->writeln('<info>SKIPPING anonymizing location.</info>');
        }

        if (
            $anonymizeUserId
            && !$this->confirmAnonymize($startDate, $endDate, 'anonymize user id')
        ) {
            $anonymizeUserId = false;
            $output->writeln('<info>SKIPPING anonymizing user id.</info>');
        }

        if (
            !empty($visitColumnsToUnset)
            && !$this->confirmAnonymize(
                $startDate,
                $endDate,
                'unset the log_visit columns "' . implode(', ', $visitColumnsToUnset) . '"'
            )
        ) {
            $visitColumnsToUnset = false;
            $output->writeln('<info>SKIPPING unset log_visit columns.</info>');
        }
        if (
            !empty($linkVisitActionColumns)
            && !$this->confirmAnonymize(
                $startDate,
                $endDate,
                'unset the log_link_visit_action columns "' . implode(
                    ', ',
                    $linkVisitActionColumns
                ) . '"'
            )
        ) {
            $linkVisitActionColumns = false;
            $output->writeln('<info>SKIPPING unset log_link_visit_action columns.</info>');
        }

        $logDataAnonymizations->setCallbackOnOutput(function ($message) use ($output) {
            $output->writeln($message);
        });
        $idLogData = $logDataAnonymizations->scheduleEntry('Command line', $idSites, $date, $anonymizeIp, $anonymizeLocation, $anonymizeUserId, $visitColumnsToUnset, $linkVisitActionColumns, $isStarted = true);
        $logDataAnonymizations->executeScheduledEntry($idLogData);

        $output->writeln('Done');

        return self::SUCCESS;
    }

    private function confirmAnonymize($startDate, $endDate, $action)
    {
        $noInteraction = $this->getInput()->getOption('no-interaction');
        if ($noInteraction) {
            return true;
        }
        $value = $this->ask(
            sprintf(
                '<question>Are you sure you want to %s for all visits between "%s" to "%s"? This action cannot be undone. Type "OK" to confirm this section.</question>',
                $action,
                $startDate,
                $endDate
            ),
            false
        );
        if ($value !== 'OK') {
            return false;
        }
        return true;
    }
}
