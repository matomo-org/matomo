#!/bin/bash
RED='\033[0;31m'
GREEN='\033[0;32m'
SET='\033[0m'

url_base="https://builds-artifacts.matomo.org/build?auth_key=$ARTIFACTS_PASS&repo=${{ github.repository }}&build_id=${{ github.run_id }}&build_entity_id=${{ github.run_number }}&branch=${{ github.event.pull_request.base.ref }}"
echo "::debug:: Uploading artifacts for UITests..."
cd ./tests/UI
tar -cvjSf processed-ui-screenshots.tar.bz2 processed-ui-screenshots
curl -X POST --data-binary processed-ui-screenshots.tar.bz2 "$url_base&artifact_name=processed-screenshots"
tar -cvjSf screenshot-diffs.tar.bz2 screenshot-diffs
curl -X POST --data-binary @screenshot-diffs.tar.bz2 "$url_base&artifact_name=screenshot-diffs"
