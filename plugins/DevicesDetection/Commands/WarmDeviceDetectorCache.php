<?php


namespace Piwik\Plugins\DevicesDetection\Commands;

use Piwik\DeviceDetector\DeviceDetectorCacheEntry;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WarmDeviceDetectorCache extends ConsoleCommand
{
    const OPTION_INPUT_FILE = 'input-file';
    const OPTION_SKIP_HEADER = 'skip-header-row';
    const OPTION_ROWS_TO_PROCESS = 'count';

    private static $userAgentsPatternsToIgnore = array(
        '/Amazon-Route53-Health-Check-Service[.]*/'
    );

    private $numCacheEntriesWritten = 0;

    protected function configure()
    {
        $this->setName('devicedetector:warmcache');
        $this->setDescription(
            'Populate the device detector cache with commonly used useragent strings, as provided in the input file.');
        $this->addArgument(self::OPTION_INPUT_FILE, InputArgument::REQUIRED, 
            'CSV file containing list of useragents to include');
        $this->addOption(
            self::OPTION_ROWS_TO_PROCESS,
            null,
            InputArgument::OPTIONAL,
            'Number of rows to process',
            0
        );
        $this->addOption(
            self::OPTION_SKIP_HEADER,
            null,
            InputArgument::OPTIONAL,
            'Whether to skip the first row',
            true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clearCacheDirectory();

        $inputFile = $this->openFile(
            $input->getArgument(self::OPTION_INPUT_FILE), 
            $input->getOption(self::OPTION_SKIP_HEADER)
        );

        $maxRowsToProcess = (int)$input->getOption(self::OPTION_ROWS_TO_PROCESS);
        $counter = 0;

        try {
            while ($data = fgetcsv($inputFile)) {
                $counter++;
                if ($maxRowsToProcess > 0 && $counter > $maxRowsToProcess) {
                    break;
                }

                $this->processUserAgent($data[0]);
            }
        } finally {
            fclose($inputFile);
        }
        $output->writeln("Written " . $this->numCacheEntriesWritten . " cache entries to file");
    }

    private function clearCacheDirectory()
    {
        $cacheDir = PIWIK_DOCUMENT_ROOT . rtrim(DeviceDetectorCacheEntry::CACHE_DIR, '/');
        $di = new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ( $ri as $file ) {
            $file->isDir() ?  rmdir($file) : unlink($file);
        }
    }

    private function openFile($filePath, $skipFirstRow)
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File $filePath not found");
        }
        $inputHandle = fopen($filePath, 'r');
        if ($inputHandle === false) {
            throw new \Exception("Could not open $filePath");
        }

        // Skip the first row
        if ($skipFirstRow) {
            fgetcsv($inputHandle);
        }
        return $inputHandle;
    }

    private function isValidUserAgentString($userAgent)
    {
        $matches = array();
        foreach (self::$userAgentsPatternsToIgnore as $pattern) {
            preg_match('/Amazon-Route53-Health-Check-Service[.]*/', $userAgent,   $matches);
            if ($matches) {
                return false;
            }
        }

        $parts = explode($userAgent, ' ');
        foreach ($parts as $part) {
            if (filter_var($part, FILTER_VALIDATE_IP) !== false) {
                return false;
            }
        }

        return true;
    }

    private function processUserAgent($userAgentStr)
    {
        $userAgentStr = trim(trim($userAgentStr, '"'));
        if (!$this->isValidUserAgentString($userAgentStr)) {
            return;
        }

        $deviceDetector = DeviceDetectorFactory::getInstance($userAgentStr, false);
        $outputArray = array(
            'bot' => $deviceDetector->getBot(),
            'brand' => $deviceDetector->getBrand(),
            'client' => $deviceDetector->getClient(),
            'device' => $deviceDetector->getDevice(),
            'model' => $deviceDetector->getModel(),
            'os' => $deviceDetector->getOs()
        );

        $outputPath = DeviceDetectorCacheEntry::getCachePath($userAgentStr);
        $this->writeUserAgent($outputPath, $outputArray);
    }

    private function writeUserAgent($filePath, $outputArray)
    {
        $content = "<?php return " . var_export($outputArray, true) . ";";
        file_put_contents($filePath, $content, LOCK_EX);
        $this->numCacheEntriesWritten++;
    }
}