<?php

if (file_exists("./phpunit.xml.dist")) {

    $IntegrationTestsPlugins = [];
    foreach (glob('../../plugins/*', GLOB_ONLYDIR) as $dir) {
        $dirname = basename($dir);
        if (is_dir('../../plugins/' . $dirname . '/tests/Integration')) {
            $IntegrationTestsPlugins[] = '../../plugins/' . $dirname . '/tests/Integration';
        }
        if (is_dir('../../plugins/' . $dirname . '/Tests/Integration')) {
            $IntegrationTestsPlugins[] = '../../plugins/' . $dirname . '/Tests/Integration';
        }
    }

    //split large tests to 3 parts
    $IntegrationTestsPluginsChunk = array_chunk($IntegrationTestsPlugins, (ceil(count($IntegrationTestsPlugins) / 3)));

    $dom = new DOMDocument;
    $dom->load("./phpunit.xml.dist");
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
    $newGenerate->save('./generated.xml');


} else {
    exit('Failed to open phpunit.xml.dist.');
}

