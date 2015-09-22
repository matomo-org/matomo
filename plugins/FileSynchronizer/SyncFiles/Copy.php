<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer\SyncFiles;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Psr\Log\LoggerInterface;

class Copy
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Copies the given source file to the target directory.
     *
     * @param string $sourceFile
     * @param string $targetDir
     * @param string $copyCommandTemplate  eg 'cp $source $directory' or 'scp $source user@host:$target'
     * @return Result
     */
    public function copy($sourceFile, $targetDir, $copyCommandTemplate)
    {
        $command = $this->buildCommandToCopyFile($sourceFile, $targetDir, $copyCommandTemplate);

        $this->logger->debug("Executing command '$command' to copy '$sourceFile' to '$targetDir'");

        try {
            exec($command . ' 2>&1', $output, $exitCode);
            $output = implode("\n", $output);
        } catch (\Exception $e) {
            $output   = $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            $exitCode = 255;
        }

        $this->logger->debug("Finished copying '$sourceFile'. Exit code: '$exitCode', output: '$output'");

        return new Result($command, $output, $exitCode);
    }

    /**
     * Similar to {@link copy()} but should be used if there is no file that can be copied but content instead.
     *
     * @param string $filename The name of the file that shall be created in the target directory. eg 'test.hash'
     * @param string $content The content of the file, eg '123456'
     * @param string $targetDir
     * @param string $copyCommandTemplate
     * @return Result
     */
    public function copyContent($filename, $content, $targetDir, $copyCommandTemplate)
    {
        $tmp = StaticContainer::get('path.tmp');
        Filechecks::dieIfDirectoriesNotWritable(array($tmp));

        $file = $tmp . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($file, $content);

        $result = $this->copy($file, $targetDir, $copyCommandTemplate);

        Filesystem::remove($file);

        return $result;
    }

    private function buildCommandToCopyFile($sourceFile, $targetDir, $copyCommandTemplate)
    {
        $search  = array('$source', '$target');
        $replace = array(escapeshellarg($sourceFile), escapeshellarg($targetDir));

        return str_replace($search, $replace, $copyCommandTemplate);
    }
}
