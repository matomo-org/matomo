#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
SET='\033[0m'

url_base="https://builds-artifacts.matomo.org/build?auth_key=$ARTIFACTS_PASS&repo=$GITHUB_REPO&build_id=$GITHUB_RUN_ID&build_entity_id=$GITHUB_RUN_NUMBER&branch=$GITHUB_BRANCH&github=true"
cd ./tests/UI
echo "[NOTE] Processed Screenshots:"
tar -cjf processed-ui-screenshots.tar.bz2 processed-ui-screenshots
curl -X POST --data-binary @processed-ui-screenshots.tar.bz2 "$url_base&artifact_name=processed-screenshots"
tar -cjf screenshot-diffs.tar.bz2 screenshot-diffs
curl -X POST --data-binary @screenshot-diffs.tar.bz2 "$url_base&artifact_name=screenshot-diffs"
echo -e "${GREEN}Uploading Finished...${SET}"
