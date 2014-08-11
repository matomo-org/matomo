#!/bin/bash

# if we're on master, check if .travis.yml is out of date. if github token is supplied we will try to auto-update,
# otherwise we just print a message and exit.
GENERATE_TRAVIS_YML_COMMAND="$GENERATE_TRAVIS_YML_COMMAND --dump=./generated.travis.yml"
if ! $GENERATE_TRAVIS_YML_COMMAND; then
    echo "generate:travis-yml failed!"

    # if building for 'latest_stable' ignore the error and continue build
    if [ "$TEST_AGAINST_CORE" == 'latest_stable' ]; then
        exit
    fi

    exit 1
fi

if [ "$PLUGIN_NAME" != "" ]; then
    EXISTING_YML_PATH=plugins/$PLUGIN_NAME/.travis.yml
else
    EXISTING_YML_PATH=.travis.yml
fi

echo "Diffing generated with existing (located at $EXISTING_YML_PATH)..."

diff $EXISTING_YML_PATH generated.travis.yml > /dev/null
DIFF_RESULT=$?

echo ""

if [ "$DIFF_RESULT" -eq "1" ]; then
    if [ "$GITHUB_USER_TOKEN" != "" ]; then
        cp generated.travis.yml .travis.yml

        LAST_COMMIT_MESSAGE=$(git log -1 HEAD --pretty=format:%s)

        grep ".travis.yml file is out of date" <<< "$LAST_COMMIT_MESSAGE" > /dev/null
        LAST_COMMIT_IS_NOT_UPDATE=$?

        if [ "$LAST_COMMIT_MESSAGE" == "" ] || [ "$LAST_COMMIT_IS_NOT_UPDATE" -eq "0" ]; then
            echo "Last commit message was '$LAST_COMMIT_MESSAGE', possible recursion or error in auto-update, aborting."
        else
            # only run auto-update for first travis job and if not a pull request
            if [ "$TRAVIS_PULL_REQUEST" == "false" ] && [[ "$TRAVIS_JOB_NUMBER" == *.1 ]]; then
                SCRIPT_DIR=$( dirname "$0" )
                $SCRIPT_DIR/configure_git.sh # re-configure in case git hasn't been configured yet

                git add .travis.yml
                git commit -m ".travis.yml file is out of date, auto-updating .travis.yml file."

                git remote set-url origin "https://$GITHUB_USER_TOKEN:@github.com/$TRAVIS_REPO_SLUG.git"

                if ! git push origin $TRAVIS_BRANCH 2> /dev/null; then
                    echo "Failed to push!"
                fi
            else
                echo "Building for pull request or not first job, skipping .travis.yml out of date check."
                echo ""
                echo "TRAVIS_PULL_REQUEST=$TRAVIS_PULL_REQUEST"
                echo "TRAVIS_JOB_NUMBER=$TRAVIS_JOB_NUMBER"
            fi
        fi

        echo ""
        echo "Generated .travis.yml:"
        echo ""
        cat generated.travis.yml
    else
        echo "${RED}Your .travis.yml file is out of date! Please update it using the generate:travis-yml command.${RESET}"
    fi

    exit 1
else
    echo ".travis.yml file is up-to-date."
fi