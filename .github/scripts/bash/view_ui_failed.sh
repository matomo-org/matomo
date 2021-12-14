#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
SET='\033[0m'

echo "::error::https://builds-artifacts.matomo.org/github/$GITHUB_REPO/$GITHUB_BRANCH/$GITHUB_RUN_ID/"
echo -e "${RED}View UI failures (if any) here:${SET}"
echo ""
echo -e "${GREEN}https://builds-artifacts.matomo.org/github/$GITHUB_REPO/$GITHUB_BRANCH/$GITHUB_RUN_ID/${SET}"
echo ""
echo -e "${RED}If the new screenshots are valid, then you can copy them over to the right directory with the command:${SET}"
echo ""
echo -e "${GREEN}./console tests:sync-ui-screenshots -a github -r matomo-org/matomo $GITHUB_RUN_ID${SET}"
