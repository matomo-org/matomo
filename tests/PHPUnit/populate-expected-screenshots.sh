#!/bin/bash

current_branch=$(git rev-parse --abbrev-ref HEAD)

# checkout branch and missing files/directories/links if needed
if ! git checkout $1; then
    echo "failed to checkout branch, aborting"
    exit 1
fi

test_files="UI/UIIntegrationTest.php
Fixtures/ManySitesImportedLogsWithXssAttempts.php
proxy/libs
proxy/plugins
proxy/tests"

for file in $test_files
do
    if [ ! -e "$file" ]; then
        git checkout master "$file"
    fi
done

# run UI tests
echo "Running UI tests..."
phpunit UI &> /dev/null

# copy processed png
echo "Copying to expected screenshot dir..."
if [ ! -d "UI/expected-ui-screenshots" ]; then
    mkdir UI/expected-ui-screenshots
fi

cp UI/processed-ui-screenshots/* UI/expected-ui-screenshots

# go back to original branch
rm populate-expected-screenshots.sh
git reset --hard
git checkout $current_branch

