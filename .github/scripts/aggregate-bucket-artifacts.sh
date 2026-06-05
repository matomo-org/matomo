#!/usr/bin/env bash
#
# Aggregates the per-bucket SystemTestsPlugins processed-files archives that
# each PHP-system-plugins bucket POSTs to builds-artifacts.matomo.org with
# artifact_name=system.plugin.bucket-N (via the artifact-name-suffix input on
# matomo-org/github-action-tests).
#
# Runs once at the end of the matrix. Downloads each bucket archive, merges
# them into one tree, and re-uploads the merged result as the canonical
# artifact_name=system.plugin so DevelopmentSyncProcessedSystemTests can keep
# fetching the merged archive at the URL it already hardcodes.
#
# Required env:
#   ARTIFACTS_PASS       upload auth key (POST query param)
#   GITHUB_REPO          owner/name
#   GITHUB_BRANCH        branch the run is for (used in both download URL path
#                        and upload POST query)
#   GITHUB_RUN_ID        used in download URL path; build_id on upload
#   GITHUB_RUN_NUMBER    build_entity_id on upload
#   BUCKET_COUNT         number of buckets to attempt to download (defaults 10).
#                        Must be >= the matrix size in PHP-system-plugins.
#                        Missing buckets above the actual count fail fast with
#                        404 and are tolerated; failing only if zero fetched.
#
set -euo pipefail

: "${ARTIFACTS_PASS:?}"
: "${GITHUB_REPO:?}"
: "${GITHUB_BRANCH:?}"
: "${GITHUB_RUN_ID:?}"
: "${GITHUB_RUN_NUMBER:?}"
bucket_count="${BUCKET_COUNT:-10}"

base_url="https://builds-artifacts.matomo.org"
auth="auth_key=$ARTIFACTS_PASS&repo=$GITHUB_REPO&build_id=$GITHUB_RUN_ID&build_entity_id=$GITHUB_RUN_NUMBER&branch=$GITHUB_BRANCH"
download_base="$base_url/$GITHUB_REPO/$GITHUB_BRANCH/$GITHUB_RUN_ID"

work=$(mktemp -d)
merged="$work/merged"
mkdir -p "$merged"
trap 'rm -rf "$work"' EXIT

# Bucket files are addressed directly by name on the artifacts server at
# {repo}/{branch}/{run_id}/system.plugin.bucket-N.tar.bz2. Loop seq 1..N and
# tolerate misses — partial uploads are still useful, matching the if: always()
# semantics in upload_artifacts.sh.
#
# Using --fail without --retry-all-errors so 4xx fails fast (a 404 is a
# permanent answer about a missing bucket, not something to retry). --retry
# alone still retries transient errors like 5xx / connection-reset.
fetched=0
missing=0
for i in $(seq 1 "$bucket_count"); do
  filename="system.plugin.bucket-$i.tar.bz2"
  url="$download_base/$filename"
  tarfile="$work/$filename"
  echo "::group::bucket $i — $url"
  if curl -sS --fail --retry 3 --output "$tarfile" "$url"; then
    tar -xjf "$tarfile" -C "$merged"
    fetched=$((fetched + 1))
  else
    echo "::warning::bucket $i unavailable (likely 404 — missing upload); skipping"
    missing=$((missing + 1))
  fi
  echo "::endgroup::"
done

if [ "$fetched" -eq 0 ]; then
  echo "::error::no bucket archives could be downloaded (0 of $bucket_count); nothing to merge"
  exit 1
fi

file_count=$(find "$merged" -type f | wc -l | tr -d ' ')
echo "::group::Merging $file_count file(s) from $fetched bucket(s) into system.plugin tarball"
ls "$merged" | head -20
echo "::endgroup::"

cd "$merged"
tar --exclude='.gitkeep' -cjf "$work/system.plugin.tar.bz2" .

echo "::group::Uploading merged archive as artifact_name=system.plugin"
curl -X POST --fail --retry 3 \
  --data-binary "@$work/system.plugin.tar.bz2" \
  "$base_url/build?$auth&artifact_name=system.plugin"
echo "::endgroup::"

echo ""
echo "Aggregation complete. Fetched $fetched of $bucket_count bucket(s); $missing missing."
echo "Merged archive: $download_base/system.plugin.tar.bz2"
