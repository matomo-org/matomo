#!/bin/bash

# initiate UI tests before starting system tests for php 5.5
if [ "$TEST_SUITE" != "SystemTests" ] || [[ "$TRAVIS_PHP_VERSION" != 5\.6* ]]; then
    echo "Not initiating UI tests (\$TEST_SUITE = $TEST_SUITE, \$TRAVIS_PHP_VERSION = $TRAVIS_PHP_VERSION)."
    exit
fi

if [ "$PIWIK_AUTOMATION" = "" ]; then
    echo "Automation details are not present, skipping UI tests."
    exit
fi

COMMIT_MESSAGE=$(git log "$TRAVIS_COMMIT" -1 --pretty=%B)

cd tests/PHPUnit/UI

UI_BRANCH="master"
git checkout $UI_BRANCH
git pull --rebase origin $UI_BRANCH

echo "$TRAVIS_COMMIT
$TRAVIS_BRANCH" > piwik_commit.txt

git add ./piwik_commit.txt
git commit -m "Travis: Initiating build for commit '$TRAVIS_COMMIT' on branch '$TRAVIS_BRANCH': $COMMIT_MESSAGE"
git remote set-url origin "https://piwik-auto-commit-bot:$PIWIK_AUTOMATION@github.com/piwik/piwik-ui-tests.git"

if ! git push origin $UI_BRANCH 2> /dev/null; then
    echo "Failed to push!"
    exit 1
fi
