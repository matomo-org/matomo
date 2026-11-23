<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use PDO;
use Piwik\Db;
use Piwik\Db\Sqlite\Connection;
use Piwik\Plugin\ConsoleCommand;

/**
 * Backs up a SQLite database while Matomo runs, or checks a copy before it is restored.
 */
class SqliteBackup extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('database:sqlite-backup');
        $this->setDescription('Copies the SQLite database to a file, consistent even while Matomo writes to it.');
        $this->addRequiredArgument('file', 'The file to write. It must not exist yet.');
        $this->addNoValueOption('verify', null, 'Only check the given file: a complete database in WAL mode, as a restore needs.');
        $this->setHelp('The copy is written to a temporary file next to the target, switched to WAL mode and checked;'
            . ' only then does it get the target\'s name, without replacing any file, so a failed backup never leaves a partial file under that name.'
            . "\n\nTo restore: stop the web server, the tracker and cron; move the database file aside together with its"
            . ' -wal, -shm and -journal files and its -locks directory; put the backup in its place; run this command'
            . ' with --verify on it; then start the services again.');
    }

    protected function doExecute(): int
    {
        $file = (string) $this->getInput()->getArgument('file');

        if ($this->getInput()->getOption('verify')) {
            $this->verify($file);
            $this->getOutput()->writeln(sprintf('<info>%s is a complete SQLite database in WAL mode.</info>', $file));
            return self::SUCCESS;
        }

        if (!Connection::isSqliteConnection(Db::get())) {
            throw new \Exception('This command backs up a SQLite database; Matomo uses another database.');
        }

        $this->backUp($file);
        $this->getOutput()->writeln(sprintf('<info>Backed up the database to %s.</info>', $file));

        return self::SUCCESS;
    }

    private function backUp(string $file): void
    {
        if (file_exists($file)) {
            throw new \Exception(sprintf('%s already exists: choose another file.', $file));
        }

        // created empty first, and private from the start: VACUUM INTO accepts an empty file and keeps its
        // permissions, and nobody else reads the copy
        $temporaryFile = $file . '.tmp-' . bin2hex(random_bytes(8));
        $umask = umask(0077);
        try {
            $handle = @fopen($temporaryFile, 'x');
        } finally {
            umask($umask);
        }
        if ($handle === false) {
            throw new \Exception(sprintf('Can\'t create %s: check that the directory exists and is writable.', $temporaryFile));
        }
        fclose($handle);

        // a default ACL on the directory overrides the umask: the file is still empty, so set and check its mode
        @chmod($temporaryFile, 0600);
        clearstatcache(true, $temporaryFile);
        if (DIRECTORY_SEPARATOR === '/' && (fileperms($temporaryFile) & 0077) !== 0) {
            @unlink($temporaryFile);
            throw new \Exception(sprintf('Can\'t make %s readable by its owner only.', $temporaryFile));
        }

        try {
            // a consistent snapshot, read while trackers keep writing
            Db::query('VACUUM INTO ?', [$temporaryFile]);

            // the copy comes out in rollback-journal mode: Matomo's database is always in WAL mode
            $copy = $this->open($temporaryFile);
            $mode = $copy->query('PRAGMA journal_mode = WAL')->fetchColumn();
            if (strtolower((string) $mode) !== 'wal') {
                throw new \Exception(sprintf('The copy can\'t use WAL mode (journal mode is %s).', $mode));
            }
            $this->quickCheck($copy);
            // closing the only connection checkpoints the copy and removes its -wal and -shm files
            $copy = null;

            $this->publish($temporaryFile, $file);
        } finally {
            $copy = null;
            foreach (['', '-wal', '-shm', '-journal'] as $suffix) {
                if (file_exists($temporaryFile . $suffix) && !@unlink($temporaryFile . $suffix)) {
                    $this->getOutput()->writeln(sprintf('<comment>Could not remove %s: remove it by hand.</comment>', $temporaryFile . $suffix));
                }
            }
        }
    }

    /**
     * Gives the copy its final name without ever replacing a file created there meanwhile: link() fails if the
     * name exists. Without hard links the backup fails rather than risk replacing a file.
     */
    private function publish(string $temporaryFile, string $file): void
    {
        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            if (file_exists($file . $suffix) || is_link($file . $suffix)) {
                // SQLite would apply them to the copy when it is opened
                throw new \Exception(sprintf('%s exists: remove it, or choose another file.', $file . $suffix));
            }
        }
        if (@link($temporaryFile, $file)) {
            return;
        }

        clearstatcache(true, $file);
        if (file_exists($file) || is_link($file)) {
            throw new \Exception(sprintf('%s was created during the backup: choose another file.', $file));
        }
        throw new \Exception(sprintf('Can\'t create %s: its file system must support hard links, which let the copy get its name without replacing a file.', $file));
    }

    private function verify(string $file): void
    {
        if (!is_file($file)) {
            throw new \Exception(sprintf('%s does not exist.', $file));
        }

        $copy = $this->open($file);
        $mode = $copy->query('PRAGMA journal_mode')->fetchColumn();
        if (strtolower((string) $mode) !== 'wal') {
            throw new \Exception(sprintf('%s is not in WAL mode (journal mode is %s).', $file, $mode));
        }
        $this->quickCheck($copy);

        foreach ([$file, dirname($file)] as $path) {
            if (!is_writable($path)) {
                throw new \Exception(sprintf('%s is not writable by this user; the web server user must be able to write it too.', $path));
            }
        }
    }

    private function open(string $file): PDO
    {
        return new PDO(Connection::getDsn($file), null, null, Connection::getDriverOptions());
    }

    private function quickCheck(PDO $copy): void
    {
        $result = $copy->query('PRAGMA quick_check')->fetchAll(PDO::FETCH_COLUMN);
        if ($result !== ['ok']) {
            throw new \Exception('The integrity check of the copy failed: ' . implode('; ', array_slice($result, 0, 5)));
        }
    }
}
