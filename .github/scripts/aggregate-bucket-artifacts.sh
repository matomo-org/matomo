#!/usr/bin/env bash
#
# Repacks the bucket processed-files directories (downloaded by
# actions/download-artifact@v4 with merge-multiple in the collect job) into one
# tarball and POSTs it to builds-artifacts.matomo.org as the canonical
# artifact_name=system.plugin — so DevelopmentSyncProcessedSystemTests keeps
# fetching the merged archive at the URL it already hardcodes.
#
# Required env:
#   ARTIFACTS_PASS       upload auth key (POST query param)
#   GITHUB_REPO          owner/name
#   GITHUB_BRANCH        branch the run is for
#   GITHUB_RUN_ID        build_id on upload
#   GITHUB_RUN_NUMBER    build_entity_id on upload
#   BUCKET_FILES_DIR     directory where bucket processed-files have been
#                        merged by actions/download-artifact. Files within are
#                        laid out as <Plugin>/tests/System/processed/<file>
#                        (the longest-common-ancestor strip done by the
#                        download action turns the on-disk plugins/<P>/tests/
#                        System/processed/<file> path into that shape).
#
set -euo pipefail

: "${ARTIFACTS_PASS:?}"
: "${GITHUB_REPO:?}"
: "${GITHUB_BRANCH:?}"
: "${GITHUB_RUN_ID:?}"
: "${GITHUB_RUN_NUMBER:?}"
: "${BUCKET_FILES_DIR:?}"

work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

if [ ! -d "$BUCKET_FILES_DIR" ] || [ -z "$(ls -A "$BUCKET_FILES_DIR" 2>/dev/null)" ]; then
  echo "::error::no bucket files found at $BUCKET_FILES_DIR; nothing to merge"
  exit 1
fi

cd "$BUCKET_FILES_DIR"

# The wildcard expansion is guarded so a bucket-set with no processed files at
# all (very unusual — would mean every test in every bucket either skipped or
# cleaned its outputs) fails loudly rather than producing an empty tarball.
shopt -s nullglob
matches=( */tests/System/processed/* )
shopt -u nullglob
if [ "${#matches[@]}" -eq 0 ]; then
  echo "::error::no */tests/System/processed/* files in $BUCKET_FILES_DIR"
  exit 1
fi

file_count=${#matches[@]}
plugin_count=$(ls -1 | wc -l | tr -d ' ')
echo "::group::Merging $file_count file(s) from $plugin_count plugin(s) into system.plugin tarball"
ls | head -20
echo "::endgroup::"

# Same flattening upload_artifacts.sh uses in github-action-tests, just
# without the leading plugins/ prefix (already stripped by download-artifact):
#   Goals/tests/System/processed/foo.xml -> Goals~~foo.xml
# DevelopmentSyncProcessedSystemTests::updatePluginsFiles globr's "*~~*" and
# explodes on "~~" to demultiplex back into plugin dirs (CoreConsole/Commands/
# DevelopmentSyncProcessedSystemTests.php:147-155).
tar --exclude='.gitkeep' -cjf "$work/system.plugin.tar.bz2" \
  "${matches[@]}" \
  --transform 's/\/tests\/System\/processed\//~~/'

echo "::group::Uploading merged archive as artifact_name=system.plugin"
base_url="https://builds-artifacts.matomo.org"
auth="auth_key=$ARTIFACTS_PASS&repo=$GITHUB_REPO&build_id=$GITHUB_RUN_ID&build_entity_id=$GITHUB_RUN_NUMBER&branch=$GITHUB_BRANCH"
curl -X POST --fail --retry 3 \
  --data-binary "@$work/system.plugin.tar.bz2" \
  "$base_url/build?$auth&artifact_name=system.plugin"
echo "::endgroup::"

echo ""
echo "Aggregation complete. Merged $file_count file(s) into system.plugin.tar.bz2"
