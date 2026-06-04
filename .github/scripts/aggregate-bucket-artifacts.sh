#!/usr/bin/env bash
#
# Aggregates the per-bucket SystemTestsPlugins processed-files archives that
# each PHP-system-plugins bucket POSTs to builds-artifacts.matomo.org with
# artifact_name=system.plugin.bucket-N (via the artifact-name-suffix input on
# matomo-org/github-action-tests).
#
# Runs once at the end of the matrix. Downloads each bucket archive, merges
# them into one tree, and re-uploads the merged result as the canonical
# artifact_name=system.plugin — so DevelopmentSyncProcessedSystemTests keeps
# fetching the merged archive at the URL it already hardcodes.
#
# Required env:
#   ARTIFACTS_PASS, GITHUB_REPO, GITHUB_BRANCH, GITHUB_RUN_ID, GITHUB_RUN_NUMBER
#
set -euo pipefail

: "${ARTIFACTS_PASS:?}"
: "${GITHUB_REPO:?}"
: "${GITHUB_BRANCH:?}"
: "${GITHUB_RUN_ID:?}"
: "${GITHUB_RUN_NUMBER:?}"

base_url="https://builds-artifacts.matomo.org"
auth="auth_key=$ARTIFACTS_PASS&repo=$GITHUB_REPO&build_id=$GITHUB_RUN_ID&build_entity_id=$GITHUB_RUN_NUMBER&branch=$GITHUB_BRANCH"

work=$(mktemp -d)
merged="$work/merged"
mkdir -p "$merged"
trap 'rm -rf "$work"' EXIT

# Discover bucket archives via the /api/ listing endpoint. SyncScreenshots.php
# already uses the same endpoint (plugins/TestRunner/Commands/SyncScreenshots.php),
# which returns a JSON object {filename: url-path}. Using discovery here means
# the aggregator survives changes to MATOMO_SYSTEM_PLUGINS_MAX_PARALLEL without
# the matomo workflow needing to keep the bucket count in sync with this script.
api_url="$base_url/api/$GITHUB_REPO/$GITHUB_RUN_NUMBER"
echo "::group::Discovering bucket archives at $api_url"
listing=$(curl -sS --fail --retry 3 --retry-all-errors "$api_url")
echo "$listing" | jq .
echo "::endgroup::"

pattern="^system\\.plugin\\.bucket-[0-9]+\\.${GITHUB_RUN_NUMBER}\\.tar\\.bz2$"
mapfile -t bucket_files < <(
  echo "$listing" \
    | jq -r --arg pat "$pattern" 'to_entries[] | select(.key | test($pat)) | .key' \
    | sort
)

if [ "${#bucket_files[@]}" -eq 0 ]; then
  echo "::error::no system.plugin.bucket-* archives found for run $GITHUB_RUN_NUMBER at $api_url"
  exit 1
fi

echo "Discovered ${#bucket_files[@]} bucket archive(s):"
printf '  %s\n' "${bucket_files[@]}"

# Tolerate partial uploads — if a bucket died before its upload step, we still
# merge what we have. Matches the if: always() semantics that
# scripts/bash/upload_artifacts.sh in github-action-tests already runs under.
missing=0
for filename in "${bucket_files[@]}"; do
  url="$base_url/$GITHUB_REPO/$filename"
  tarfile="$work/$filename"
  echo "::group::Downloading $filename"
  if curl -sS --fail --retry 3 --retry-all-errors --output "$tarfile" "$url"; then
    tar -xjf "$tarfile" -C "$merged"
  else
    echo "::warning::failed to download $filename; merge will be partial"
    missing=$((missing + 1))
  fi
  echo "::endgroup::"
done

if [ -z "$(ls -A "$merged" 2>/dev/null)" ]; then
  echo "::error::no bucket archives could be extracted; nothing to merge"
  exit 1
fi

file_count=$(find "$merged" -type f | wc -l | tr -d ' ')
echo "::group::Merging $file_count file(s) into system.plugin tarball"
ls "$merged" | head -20
echo "::endgroup::"

cd "$merged"
tar --exclude='.gitkeep' -cjf "$work/system.plugin.tar.bz2" .

echo "::group::Uploading merged archive as artifact_name=system.plugin"
curl -X POST --fail --retry 3 --retry-all-errors \
  --data-binary "@$work/system.plugin.tar.bz2" \
  "$base_url/build?$auth&artifact_name=system.plugin"
echo "::endgroup::"

echo ""
echo "Aggregation complete. ${missing} bucket(s) missing of ${#bucket_files[@]} discovered."
echo "Merged archive: $base_url/$GITHUB_REPO/system.plugin.$GITHUB_RUN_NUMBER.tar.bz2"
