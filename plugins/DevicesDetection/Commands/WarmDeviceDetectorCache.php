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
    protected function configure()
    {
        $this->setName('devicedetector:warmcache');
        $this->setDescription(
            'Populate the device detector cache with commonly used useragent strings, as provided in the input file.');
        $this->addArgument('inputFile', InputArgument::REQUIRED, 
            'CSV file containing list of useragents to include');
        $this->addOption(
            'count',
            'c',
            InputArgument::OPTIONAL,
            'Number of rows to process',
            0
        );
        $this->addOption(
            'skipHeaderRow',
            's',
            InputArgument::OPTIONAL,
            'Whether to skip the first row',
            true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFile = $this->openFile($input->getArgument('inputFile'), $input->getOption('skipHeaderRow'));

        $maxRowsToProcess = (int)$input->getOption('count');
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

    private function processUserAgent($userAgentStr)
    {
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
        $outputFile = fopen($filePath, 'w');
        if ($outputFile === false) {
            throw new \Exception("Could not write to $filePath");
        }

        try {
            fwrite($outputFile,"<?php return " . var_export($outputArray, true) . ";");
        } finally {
            fclose($outputFile);
        }
    }
}