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
    if [ "$TEST_SUITE" = "UITests" ];
    then
        branch_name="ui-tests.$TRAVIS_BRANCH"
        url_base="http://builds-artifacts.piwik.org/upload.php?auth_key=$ARTIFACTS_PASS&build_id=$TRAVIS_JOB_NUMBER"

        if [ -n "$PLUGIN_NAME" ];
        then
            branch_name="$branch_name.$PLUGIN_NAME"
            url_base="$url_base&protected=1"
        fi

        url_base="$url_base&branch=$branch_name"

        echo "Uploading artifacts for $TEST_SUITE..."

        base_dir=`pwd`
        if [ -n "$PLUGIN_NAME" ];
        then
            cd "./plugins/$PLUGIN_NAME/Test/UI"
        else
            cd ./tests/PHPUnit/UI
        fi

        # upload processed tarball
        tar -cjf processed-ui-screenshots.tar.bz2 processed-ui-screenshots --exclude='.gitkeep'
        curl -X POST --data-binary @processed-ui-screenshots.tar.bz2 "$url_base&artifact_name=processed-ui-screenshots"

        # upload diff tarball if it exists
        cd $base_dir/tests/PHPUnit/UI
        if [ -d "./screenshot-diffs" ];
        then
            echo "Uploading screenshot diffs..."

            echo "[NOTE] screenshot diff dir:"
            echo "`pwd`/screenshot-diffs"

            echo "[NOTE] uploading following diffs:"
            ls screenshot-diffs

            tar -cjf screenshot-diffs.tar.bz2 screenshot-diffs
            curl -X POST --data-binary @screenshot-diffs.tar.bz2 "$url_base&artifact_name=screenshot-diffs"

            if [ -n "$PLUGIN_NAME" ];
            then
                diffviewer_url="http://builds-artifacts.piwik.org/protected/$branch_name/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
            else
                diffviewer_url="http://builds-artifacts.piwik.org/$branch_name/$TRAVIS_JOB_NUMBER/screenshot-diffs/diffviewer.html"
            fi

            echo "View UI failures (if any) here: $diffviewer_url"
        fi
    else
        echo "No artifacts for $TEST_SUITE tests."
        exit
    fi
fi