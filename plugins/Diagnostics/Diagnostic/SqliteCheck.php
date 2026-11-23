<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ExposureProbe;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Checks a SQLite database: library version, journal mode, durability, file permissions, whether the web
 * server serves the files, and the unused actions purge.
 */
class SqliteCheck implements Diagnostic
{
    /**
     * Consecutive unused actions purges that stopped early before it is reported.
     */
    public const INCOMPLETE_PURGES_WARNING = 3;

    private Translator $translator;

    private ExposureProbe $exposureProbe;

    public function __construct(Translator $translator, ExposureProbe $exposureProbe)
    {
        $this->translator = $translator;
        $this->exposureProbe = $exposureProbe;
    }

    public function execute()
    {
        if (!SettingsPiwik::isMatomoInstalled() || !Connection::isSqliteConnection(Db::get())) {
            return [];
        }

        $result = new DiagnosticResult('SQLite');
        $result->addItem($this->checkLibrary());
        $result->addItem($this->checkJournalMode());
        $result->addItem($this->checkSynchronous());
        foreach ($this->checkFilesWritable() as $item) {
            $result->addItem($item);
        }
        $result->addItem($this->checkExposure());
        $purge = $this->checkUnusedActionsPurge();
        if ($purge !== null) {
            $result->addItem($purge);
        }

        return [$result];
    }

    private function checkLibrary(): DiagnosticResultItem
    {
        $version = (string) Db::fetchOne('SELECT sqlite_version()');
        $isAttested = !empty(Config::getInstance()->database['sqlite_wal_reset_fix_attested']);

        switch (Connection::getRuntimeQualification($version, $isAttested)) {
            case Connection::RUNTIME_UNSUPPORTED:
                return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate(
                    'Diagnostics_SqliteVersionUnsupported',
                    [$version, Connection::MINIMUM_VERSION]
                ));
            case Connection::RUNTIME_UNQUALIFIED:
                return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate(
                    'Diagnostics_SqliteVersionUnqualified',
                    [$version, 'sqlite_wal_reset_fix_attested = 1']
                ));
            default:
                if (!Connection::hasMathFunctions()) {
                    return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate('Diagnostics_SqliteNoMathFunctions', [$version]));
                }
                return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, $this->translator->translate('Diagnostics_SqliteVersion', [$version]));
        }
    }

    private function checkJournalMode(): DiagnosticResultItem
    {
        $mode = strtolower((string) Db::fetchOne('PRAGMA journal_mode'));
        if ($mode === 'wal') {
            return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, 'journal_mode = wal');
        }

        return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate('Diagnostics_SqliteJournalMode', [$mode]));
    }

    private function checkSynchronous(): DiagnosticResultItem
    {
        // 2 is FULL: a commit survives a power loss
        if ((int) Db::fetchOne('PRAGMA synchronous') === 2) {
            return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, 'synchronous = FULL');
        }

        return new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $this->translator->translate('Diagnostics_SqliteSynchronous'));
    }

    /**
     * @return DiagnosticResultItem[]
     */
    private function checkFilesWritable(): array
    {
        $file = Connection::getDatabaseFile(Config::getInstance()->database);

        $notWritable = [];
        foreach ([$file, dirname($file), $file . '-wal', $file . '-shm', $file . '-locks'] as $path) {
            if (file_exists($path) && !is_writable($path)) {
                $notWritable[] = $path;
            }
        }

        if ($notWritable === []) {
            return [new DiagnosticResultItem(DiagnosticResult::STATUS_OK, $this->translator->translate('Diagnostics_SqliteFilesWritable'))];
        }

        return array_map(function ($path) {
            return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate('Diagnostics_SqliteFileNotWritable', [$path]));
        }, $notWritable);
    }

    private function checkExposure(): DiagnosticResultItem
    {
        $file = Connection::getDatabaseFile(Config::getInstance()->database);
        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!preg_match('~^https?://~i', $baseUrl)) {
            // e.g. on the command line without --matomo-domain: nothing can be requested
            return new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $this->translator->translate('Diagnostics_SqliteServedNotChecked'));
        }

        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : null;
        $result = $this->exposureProbe->probe($file, $baseUrl, $documentRoot);

        switch ($result['status']) {
            case ExposureProbe::OUTSIDE_WEB_ROOT:
                return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, $this->translator->translate('Diagnostics_SqliteOutsideWebRoot'));
            case ExposureProbe::PROTECTED:
                return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, $this->translator->translate('Diagnostics_SqliteNotServed'));
            case ExposureProbe::UNVERIFIED:
                return new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $this->translator->translate('Diagnostics_SqliteServedUnverified'));
        }

        $urls = [];
        foreach ($result['urls'] as $url => $status) {
            if ($status !== ExposureProbe::PROTECTED) {
                $urls[] = htmlspecialchars($url, ENT_QUOTES) . ': ' . $status;
            }
        }
        $message = $result['status'] === ExposureProbe::EXPOSED ? 'Diagnostics_SqliteServed' : 'Diagnostics_SqliteServedUnknown';

        return new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $this->translator->translate($message, [implode('<br/>', $urls)]));
    }

    private function checkUnusedActionsPurge(): ?DiagnosticResultItem
    {
        $incompleteRuns = (int) Option::get(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE);
        if ($incompleteRuns < self::INCOMPLETE_PURGES_WARNING) {
            return null;
        }

        return new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $this->translator->translate(
            'Diagnostics_SqliteUnusedActionsPurgeIncomplete',
            [$incompleteRuns, './console core:run-scheduled-tasks "Piwik\\Plugins\\PrivacyManager\\Tasks.deleteLogData"']
        ));
    }
}
