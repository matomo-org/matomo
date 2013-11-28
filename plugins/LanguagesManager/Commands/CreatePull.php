<?php
/**
 * Piwik - Open source web analytics
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package  CoreConsole
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class CreatePull extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:createpull')
            ->setDescription('Updates translation files')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'oTrance username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'oTrance password')
            ->addOption('plugin', 'P', InputOption::VALUE_OPTIONAL, 'optional name of plugin to update translations for');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $changes = shell_exec('git status --porcelain -uno');

        if (!empty($changes)) {

            $output->writeln("You have uncommited changes. Creating pull request is only available with a clean working directory");
            return;
        }

        $unpushedCommits = shell_exec('git log origin/master..HEAD');

        if (!empty($unpushedCommits)) {

            $output->writeln("You have unpushed commits. Creating pull request is only available with a clean working directory");
            return;
        }

        chdir(PIWIK_DOCUMENT_ROOT);

        shell_exec('
            git checkout master > /dev/null 2>&1
            git pull > /dev/null 2>&1
            git submodule init > /dev/null 2>&1
            git submodule update > /dev/null 2>&1
        ');

        $plugin = $input->getOption('plugin');
        if (!empty($plugin)) {

            chdir(PIWIK_DOCUMENT_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin);
            shell_exec('
                git checkout master > /dev/null 2>&1
                git pull > /dev/null 2>&1
            ');
        }

        // check if branch exists localy and track it if not
        $branch = shell_exec('git branch | grep translationupdates');

        if (empty($branch)) {

            shell_exec('git checkout -b translationupdates origin/translationupdates');
        }

        // switch to branch and update it to latest master
        shell_exec('
            git checkout translationupdates > /dev/null 2>&1
            git merge master > /dev/null 2>&1
            git push origin translationupdates > /dev/null 2>&1
        ');

        // update translation files
        $command = $this->getApplication()->find('translations:update');
        $arguments = array(
            'command'    => 'translations:update',
            '--username' => $input->getOption('username'),
            '--password' => $input->getOption('password'),
            '--plugin'   => $plugin
        );
        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($input->isInteractive());
        $command->run($inputObject, $output);

        shell_exec('git add lang/. > /dev/null 2>&1');

        $changes = shell_exec('git status --porcelain -uno');

        if (empty($changes)) {

            $output->writeln("Nothing changed. Everything is already up to date.");
            shell_exec('git checkout master > /dev/null 2>&1');
            return;
        }

        $fileCount = 0;
        $message   = '';
        API::unsetInstance(); // reset languagemanager api (to force refresh of data)

        $stats = shell_exec('git diff --numstat HEAD lang');

        preg_match_all('/([0-9]+)\t([0-9]+)\tlang\/([a-z]{2,3})\.json/', $stats, $lineChanges);

        $addedLinesSum = 0;
        if (!empty($lineChanges[1])) {
            $addedLinesSum = array_sum($lineChanges[1]);
        }

        $linesSumByLang = array();
        for($i=0; $i<count($lineChanges[0]); $i++) {
            $linesSumByLang[$lineChanges[3][$i]] = $lineChanges[1][$i];
        }

        preg_match_all('/M  lang\/([a-z]{2,3})\.json/', $changes, $modifiedFiles);
        preg_match_all('/A  lang\/([a-z]{2,3})\.json/', $changes, $addedFiles);

        $lnaguageCodesTouched = array();
        if (!empty($modifiedFiles[1])) {
            foreach ($modifiedFiles[1] AS $modifiedFile) {
                $fileCount++;
                $languageInfo = $this->getLanguageInfoByIsoCode($modifiedFile);
                $message .= sprintf('- Updated %s (%s changes / %s translated)\n', $languageInfo['english_name'], $linesSumByLang[$modifiedFile], $languageInfo['percentage_complete']);
            }
            $lnaguageCodesTouched = $modifiedFiles[1];
        }

        if (!empty($addedFiles[1])) {
            foreach ($addedFiles[1] AS $addedFile) {
                $fileCount++;
                $languageInfo = $this->getLanguageInfoByIsoCode($addedFile);
                $message .= sprintf('- Added %s (%s changes / %s translated)\n', $languageInfo['english_name'], $linesSumByLang[$addedFile], $languageInfo['percentage_complete']);
            }
            $lnaguageCodesTouched = array_merge($lnaguageCodesTouched, $addedFiles[1]);
        }

        $title = sprintf(
            'Updated %s strings in %u languages (%s)',
            $addedLinesSum,
            $fileCount,
            implode(', ', $lnaguageCodesTouched)
        );

        shell_exec('git commit -m "language update ${pluginName} refs #3430"');
        shell_exec('git push');
        shell_exec('git checkout master > /dev/null 2>&1');

        $this->createPullRequest($output, $title, $message);
    }

    private function getLanguageInfoByIsoCode($isoCode)
    {
        $languages = API::getInstance()->getAvailableLanguagesInfo();
        foreach ($languages AS $languageInfo) {
            if ($languageInfo['code'] == $isoCode) {
                return $languageInfo;
            }
        }
        return array();
    }

    private function createPullRequest(OutputInterface $output, $title, $message)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        while (true) {

            $username = $dialog->ask($output, 'Please provide your github username (to create a pull request using Github API): ');

            $returnCode = shell_exec('curl \
                 -X POST \
                 -k \
                 --silent \
                 --write-out %{http_code} \
                 --stderr /dev/null \
                 -o /dev/null \
                 -u '.$username.' \
                 --data "{\"title\":\"[automatic translation update] '.$title.'\",\"body\":\"'.$message.'\",\"head\":\"translationupdates\",\"base\":\"master\"}" \
                 -H "Accept: application/json" \
                 https://api.github.com/repos/piwik/piwik/pulls');

            switch ($returnCode) {
                case 401:
                    $output->writeln("Pull request failed. Bad credentials... Please try again");
                    continue;

                case 422:
                    $output->writeln("Pull request failed. Unprocessable Entity. Maybe a pull request was already created before.");
                    return;

                case 201:
                case 200:
                    $output->writeln("Pull request successfully created.");
                    return;

                default:
                    $output->writeln("Pull request failed... Please try again");
            }
        }
    }
}