<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Unzip;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class FetchFromOTrance extends ConsoleCommand
{
    const DOWNLOADPATH = 'tmp/oTrance';

    protected function configure()
    {
        $this->setName('translations:fetch')
             ->setDescription('Fetches translations files from oTrance to '.self::DOWNLOADPATH)
             ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'oTrance username')
             ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'oTrance password')
             ->addOption('keep-english', 'k', InputOption::VALUE_NONE, 'keep english file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting to fetch latest language pack");

        $dialog = $this->getHelperSet()->get('dialog');

        $cookieFile = self::getDownloadPath() . DIRECTORY_SEPARATOR . 'cookie.txt';
        @unlink($cookieFile);

        $username = $input->getOption('username');
        $password = $input->getOption('password');

        while (!file_exists($cookieFile)) {
            if (empty($username)) {
                $username = $dialog->ask($output, 'What is your oTrance username? ');
            }

            if (empty($password)) {
                $password = $dialog->askHiddenResponse($output, 'What is your oTrance password? ');
            }

            // send login request to oTrance and save the login cookie
            $curl = curl_init('http://translations.piwik.org/public/index/login');
            curl_setopt($curl, CURLOPT_POSTFIELDS, sprintf("user=%s&pass=%s&autologin=1", $username, $password));
            curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);

            if (strpos(file_get_contents($cookieFile), 'oTranCe_autologin') !== false) {
                break;
            }

            $username = null;
            $password = null;
            @unlink($cookieFile);
            $output->writeln("Invalid oTrance credentials. Please try again...");
        }

        // send request to create a new download package using the cookie file
        $createNewPackage = true;
        if ($input->isInteractive()) {
            $createNewPackage = $dialog->askConfirmation($output, 'Shall we create a new language pack? ');
        }

        if ($createNewPackage) {

            $curl = curl_init('http://translations.piwik.org/public/export/update.all');
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);
        }

        // request download page to search for available packages
        $curl = curl_init('http://translations.piwik.org/public/downloads/');
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        preg_match_all('/language\_pack\-[0-9]{8}\-[0-9]{6}\.tar\.gz/i', $response, $matches);

        if (empty($matches[0])) {

            $output->writeln("No packages found for download. Please try again.");
            return;
        }

        $downloadPackage = array_shift($matches[0]);

        $continueWithPackage = true;
        if ($input->isInteractive()) {
            $continueWithPackage = $dialog->askConfirmation($output, "Found language pack $downloadPackage. Proceed? ");
        }

        if (!$continueWithPackage) {

            $output->writeln('Aborted.');
            return;
        }

        // download language pack
        $packageHandle = fopen(self::getDownloadPath() . DIRECTORY_SEPARATOR . 'language_pack.tar.gz', 'w');
        $curl = curl_init('http://translations.piwik.org/public/downloads/download/file/'.$downloadPackage);
        curl_setopt($curl, CURLOPT_COOKIEFILE, self::getDownloadPath() . DIRECTORY_SEPARATOR . 'cookie.txt');
        curl_setopt($curl, CURLOPT_FILE, $packageHandle);
        curl_exec($curl);
        curl_close($curl);

        @unlink($cookieFile);

        $output->writeln("Extracting package...");

        $unzipper = Unzip::factory('tar.gz', self::getDownloadPath() . DIRECTORY_SEPARATOR . 'language_pack.tar.gz');
        $unzipper->extract(self::getDownloadPath());

        if (!$input->getOption('keep-english')) {
            @unlink(self::getDownloadPath() . DIRECTORY_SEPARATOR . 'en.php');
            @unlink(self::getDownloadPath() . DIRECTORY_SEPARATOR . 'en.json');
        }
        @unlink(self::getDownloadPath() . DIRECTORY_SEPARATOR . 'language_pack.tar.gz');

        $filesToConvert = _glob(self::getDownloadPath() . DIRECTORY_SEPARATOR . '*.php');

        $output->writeln("Converting downloaded php files to json");

        $progress = $this->getHelperSet()->get('progress');

        $progress->start($output, count($filesToConvert));
        foreach ($filesToConvert as $filename) {

            require_once $filename;
            $basename = explode(".", basename($filename));
            $nested = array();
            foreach ($translations as $key => $value) {
                list($plugin, $nkey) = explode("_", $key, 2);
                $nested[$plugin][$nkey] = $value;
            }
            $translations = $nested;
            $data = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $newFile = sprintf("%s/%s.json", self::getDownloadPath(), $basename[0]);
            file_put_contents($newFile, $data);
            @unlink($filename);

            $progress->advance();
        }

        $progress->finish();

        $output->writeln("Finished fetching new language files from oTrance");
    }

    public static function getDownloadPath() {

        $path = PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . self::DOWNLOADPATH;

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }
}
