#!/bin/bash

# only initiate UI tests after integration tests for php 5.5 are completed
if [ "$TEST_SUITE" != "IntegrationTests" ] || [[ "$TRAVIS_PHP_VERSION" != 5\.5* ]]; then
    echo "Not initiating UI tests (\$TEST_SUITE = $TEST_SUITE, \$TRAVIS_PHP_VERSION = $TRAVIS_PHP_VERSION)."
    exit
fi

if [ "$PIWIK_AUTOMATION" = "" ]; then
    echo "Automation details are not present, skipping UI tests."
    exit
fi

git submodule update

git checkout "$TRAVIS_BRANCH"
COMMIT_MESSAGE=$(git log "$TRAVIS_COMMIT" -1 --pretty=%B)

cd tests/PHPUnit/UI

git checkout master
git pull --rebase origin master

echo "$TRAVIS_COMMIT
$TRAVIS_BRANCH" > piwik_commit.txt

git config --global user.email "hello@piwik.org"
git config --global user.name "Piwik Automation"

git add ./piwik_commit.txt
git commit -m "Travis: Initiating build for commit '$TRAVIS_COMMIT' on branch '$TRAVIS_BRANCH': $COMMIT_MESSAGE"
git remote set-url origin "https://piwik-auto-commit-bot:$PIWIK_AUTOMATION@github.com/piwik/piwik-ui-tests.git"

if ! git push origin master 2> /dev/null; then
    echo "Failed to push!"
    exit 1
fi