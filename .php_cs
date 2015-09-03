<?php
$finder = Symfony\CS\Finder\DefaultFinder::create();
$finder->in(__DIR__);
$finder->exclude([
    'config',
    'core',
    'js',
    'lang',
    'libs', 
    'misc',
    'plugins',
    'tests',
    'tmp',
    'vendor'
]);

$config = Symfony\CS\Config\Config::create();
$config->setUsingCache(true);
$config->finder($finder);
$config->level(Symfony\CS\FixerInterface::PSR2_LEVEL);
$config->fixers([
    'unused_use'
]);

return $config;
