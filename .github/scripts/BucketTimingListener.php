<?php

declare(strict_types=1);

namespace Matomo\CI;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use ReflectionClass;

/**
 * Appends "BUCKET-TIMING <seconds> <tests> <file>" per test class to a file. Unlike a JUnit log, the
 * time runs from suite start to suite end, so it includes setUpBeforeClass, where fixtures are built.
 */
class BucketTimingListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /** @var string */
    private $outFile;

    /** @var string */
    private $root;

    /** @var array<string, float> */
    private $started = [];

    public function __construct(string $outFile, string $root)
    {
        $this->outFile = $outFile;
        $this->root = rtrim($root, '/') . '/';
    }

    public function startTestSuite(TestSuite $suite): void
    {
        if (class_exists($suite->getName(), false)) {
            $this->started[$suite->getName()] = microtime(true);
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $name = $suite->getName();
        if (!isset($this->started[$name])) {
            return;
        }
        $file = (string) (new ReflectionClass($name))->getFileName();
        if (str_starts_with($file, $this->root)) {
            $file = substr($file, strlen($this->root));
        }
        $line = sprintf("BUCKET-TIMING\t%.2f\t%d\t%s\n", microtime(true) - $this->started[$name], count($suite), $file);
        if (file_put_contents($this->outFile, $line, FILE_APPEND) === false) {
            fwrite(STDERR, "BucketTimingListener: cannot write {$this->outFile}\n");
        }
        unset($this->started[$name]);
    }
}
