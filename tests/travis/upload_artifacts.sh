#!/bin/bash

if [ "$TEST_SUITE" = "IntegrationTests" ];
then
    url="http://builds-artifacts.piwik.org/upload.php?auth_key=$ARTIFACTS_PASS&artifact_name=processed&branch=$TRAVIS_BRANCH&build_id=$TRAVIS_JOB_NUMBER"

    echo "Uploading artifacts for $TEST_SUITE..."

    cd ./tests/PHPUnit/Integration

    # upload processed tarball
    tar -cjf processed.tar.bz2 processed --exclude='.gitkeep'
    curl -X POST --data-binary @processed.tar.bz2 "$url"
else
    if [ "$TEST_DIR" = "UI" ];
    then
        url_base="http://builds-artifacts.piwik.org/upload.php?auth_key=$ARTIFACTS_PASS&branch=ui-tests.$TRAVIS_BRANCH&build_id=$TRAVIS_JOB_NUMBER"

        echo "Uploading artifacts for $TEST_DIR..."

        cd ./tests/PHPUnit/UI

        # upload processed tarball
        tar -cjf processed-ui-screenshots.tar.bz2 processed-ui-screenshots --exclude='.gitkeep'
        curl -X POST --data-binary @processed-ui-screenshots.tar.bz2 "$url_base&artifact_name=processed-ui-screenshots"

        # upload diff tarball if it exists
        if [ -d "./screenshot-diffs" ];
        then
            echo "Uploading screenshot diffs..."
            
            tar -cjf screenshot-diffs.tar.bz2 screenshot-diffs
            curl -X POST --data-binary @screenshot-diffs.tar.bz2 "$url_base&artifact_name=screenshot-diffs"

            echo "View UI failures (if any) here: http://builds-artifacts.piwik.org/ui-tests.master/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
        fi
    else
        echo "No artifacts for $TEST_SUITE tests."
        exit
    fi
fi