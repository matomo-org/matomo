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
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Checks a SQLite database: library version, journal mode, durability, file permissions and the unused
 * actions purge.
 */
class SqliteCheck implements Diagnostic
{
    /**
     * Consecutive unused actions purges that stopped early before it is reported.
     */
    public const INCOMPLETE_PURGES_WARNING = 3;

    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
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
