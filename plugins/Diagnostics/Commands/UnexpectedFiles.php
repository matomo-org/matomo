<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Development;
use Piwik\FileIntegrity;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Diagnostic command that finds all unexpected files in the Matomo installation directory and provides an option to
 * delete them
 *
 * ./console diagnostics:unexpected-files                           # show a list of unexpected files
 * ./console diagnostics:unexpected-files --delete                  # delete the unexpected files with confirmation
 * ./console diagnostics:unexpected-files --delete --no-interaction # delete the unexpected files without confirmation
 *
 */
class UnexpectedFiles extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:unexpected-files')
            ->setDescription('Show a list of unexpected files found in the Matomo installation directory and optionally delete them.')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete all the unexpected files');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // Prevent running in development mode
        if (Development::isEnabled()) {
           $output->writeln("Aborting - this command cannot be used in development mode as it requires a release manifest file");
           return 1;
        }

        // Prevent running if there is no release manifest file
        $manifest = PIWIK_INCLUDE_PATH . '/config/manifest.inc.php';
        if (!file_exists($manifest)) {
           $output->writeln("Release manifest file '".$manifest."' not found.");
           $output->writeln("Aborting - this command can only be used when a release manifest file is present.");
           return 1;
        }


        $delete = $input->getOption('delete');
        if ($delete) {

            $output->writeln("<info>Preparing to delete all unexpected files from the Matomo installation directory</info>");

            if(!$this->askForDeleteConfirmation($input, $output)) {
                $output->writeln("Aborted - no files were deleted");
                return 1;
            }

            return $this->runUnexpectedFiles($output, true);
        }

        return $this->runUnexpectedFiles($output, false);
    }

    /**
     * Handle unexpected files command options
     *
     * @param OutputInterface $output
     * @param bool $delete
     *
     * @return int
     */
    private function runUnexpectedFiles(OutputInterface $output, bool $delete = false): int
    {

        // A list of files that should never be deleted under any circumstances, this acts as a backup safety check
        // for the FileIntegrity class which should already be excluding these files.
        $excludedFiles = [
            '/^config\/config.ini.php$/',        // main config file
            '/^config\/common.config.ini.php$/', // multi-tenant config file
            '/\.htaccess$/',                     // apache directory access rules
            '/^config\/config.php$/',            // DI customisation
            '/^misc\/.*$/'                       // everything in the misc/ directory (geo databases, multi-tenant, etc)
        ];

        $files = FileIntegrity::getUnexpectedFilesList();
        $fails = 0;

        foreach ($files as $f) {

            foreach ($excludedFiles as $ef) {
                if(preg_match($ef, $f)) {
                    continue 2;
                }
            }

            $fileName = realpath($f);

            if ($delete) {
                if (Filesystem::deleteFileIfExists($fileName)) {
                    $output->writeln("Deleted unexpected file '".$fileName);
                } else {
                    $output->writeln("Failed to delete unexpected file '".$fileName);
                    $fails++;
                }
            } else {
                $output->writeln($fileName);
            }
        }
        if ($delete && $fails) {
            $output->writeln("Failed to delete ".$fails." unexpected files");
            return 1;
        }
        return 0;
    }

    /**
     * Interact with the user to confirm the deletion
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function askForDeleteConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        if (!$input->isInteractive()) {
            return true;
        }

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('<comment>You are about to delete files. This action cannot be undone, are you sure you want to continue? (Y/N)</comment> ', false);

        return $helper->ask($input, $output, $question);
    }

}
