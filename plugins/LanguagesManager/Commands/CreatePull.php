<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class CreatePull extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:createpull')
            ->setDescription('Updates translation files')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Transifex username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Transifex password')
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
            git checkout -f master > /dev/null 2>&1
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
            git checkout -f translationupdates > /dev/null 2>&1
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

        if (empty($plugin)) {
            foreach (Update::getPluginsInCore() as $pluginName) {
                shell_exec(sprintf('git add plugins/%s/lang/. > /dev/null 2>&1', $pluginName));
            }
        }

        $changes = shell_exec('git status --porcelain -uno');

        if (empty($changes)) {

            $output->writeln("Nothing changed. Everything is already up to date.");
            shell_exec('git checkout master > /dev/null 2>&1');
            return;
        }

        API::unsetInstance(); // reset languagemanager api (to force refresh of data)

        $stats = shell_exec('git diff --numstat HEAD');

        preg_match_all('/([0-9]+)\t([0-9]+)\t[a-zA-Z\/]*lang\/([a-z]{2,3}(?:-[a-z]{2,3})?)\.json/', $stats, $lineChanges);

        $addedLinesSum = 0;
        if (!empty($lineChanges[1])) {
            $addedLinesSum = array_sum($lineChanges[1]);
        }

        $linesSumByLang = array();
        $lineChangesCount = count($lineChanges[0]);
        for ($i = 0; $i < $lineChangesCount; $i++) {
            @$linesSumByLang[$lineChanges[3][$i]] += $lineChanges[1][$i];
        }

        preg_match_all('/M  [a-zA-Z\/]*lang\/([a-z]{2,3}(?:-[a-z]{2,3})?)\.json/', $changes, $modifiedFiles);
        preg_match_all('/A  [a-zA-Z\/]*lang\/([a-z]{2,3}(?:-[a-z]{2,3})?)\.json/', $changes, $addedFiles);

        $messages = array();

        $languageCodesTouched = array();
        if (!empty($addedFiles[1])) {
            foreach ($addedFiles[1] as $addedFile) {
                $languageInfo = $this->getLanguageInfoByIsoCode($addedFile);
                $messages[$addedFile] = sprintf('- Added %s (%s changes / %s translated)\n', $languageInfo['english_name'], $linesSumByLang[$addedFile], $languageInfo['percentage_complete']);
            }
            $languageCodesTouched = array_merge($languageCodesTouched, $addedFiles[1]);
        }

        if (!empty($modifiedFiles[1])) {
            foreach ($modifiedFiles[1] as $modifiedFile) {
                $languageInfo = $this->getLanguageInfoByIsoCode($modifiedFile);
                $messages[$modifiedFile] = sprintf('- Updated %s (%s changes / %s translated)\n', $languageInfo['english_name'], $linesSumByLang[$modifiedFile], $languageInfo['percentage_complete']);
            }
            $languageCodesTouched = array_merge($languageCodesTouched, $modifiedFiles[1]);
        }

        $message = implode('', $messages);

        $languageCodesTouched = array_unique($languageCodesTouched, SORT_REGULAR);

        $title = sprintf(
            'Updated %s strings in %u languages (%s)',
            $addedLinesSum,
            count($languageCodesTouched),
            implode(', ', $languageCodesTouched)
        );

        shell_exec('git commit -m "language update ${pluginName}"');
        shell_exec('git push');
        shell_exec('git checkout master > /dev/null 2>&1');

        $this->createPullRequest($output, $title, $message);
    }

    private function getLanguageInfoByIsoCode($isoCode)
    {
        $languages = API::getInstance()->getAvailableLanguagesInfo();
        foreach ($languages as $languageInfo) {
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
