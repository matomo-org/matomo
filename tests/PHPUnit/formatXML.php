<?php

$pathDist = realpath(dirname(__FILE__)) . "/phpunit.xml.dist";
$path = realpath(dirname(__FILE__)) . "/phpunit.xml";

if (file_exists($pathDist)) {
    $IntegrationTestsPlugins = [];
    foreach (glob(realpath(dirname(__FILE__)).'/../../plugins/*', GLOB_ONLYDIR) as $dir) {
        $dirname = basename($dir);
        if (is_dir(realpath(dirname(__FILE__)).'/../../plugins/' . $dirname . '/tests/Integration')) {
            $IntegrationTestsPlugins[] = '../../plugins/' . $dirname . '/tests/Integration';
        }
        if (is_dir(realpath(dirname(__FILE__)).'/../../plugins/' . $dirname . '/Tests/Integration')) {
            $IntegrationTestsPlugins[] = '../../plugins/' . $dirname . '/Tests/Integration';
        }
    }

    //split large tests to 3 parts
    $IntegrationTestsPluginsChunk = array_chunk($IntegrationTestsPlugins, (ceil(count($IntegrationTestsPlugins) / 3)));

    $dom = new DOMDocument;
    $dom->load($pathDist);
    foreach ($dom->getElementsByTagName('testsuite') as $testSuit) {
        if ($testSuit->getAttribute('name') === "IntegrationTestsPlugins") {
            $parent = $testSuit->parentNode;
            $parent->removeChild($testSuit);
            foreach ($IntegrationTestsPluginsChunk as $key => $smaller) {
                $testSuit = $dom->createElement('testsuite');
                $domAttribute = $dom->createAttribute('name');
                $domAttribute->value = "IntegrationTestsPlugins" . $key;
                $testSuit->appendChild($domAttribute);
                $newPlugin = $parent->appendChild($testSuit);
                foreach ($smaller as $dir) {
                    $domDirAttribute = $dom->createElement('directory', $dir);
                    $newPlugin->appendChild($domDirAttribute);
                }

            }
        }
    }
    $generated = $dom->saveXML();

    $newGenerate = new DOMDocument();
    $newGenerate->loadXML($generated);
    $newGenerate->save($path);


} else {
    exit('Failed to open ' . $pathDist);
}

