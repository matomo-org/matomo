if [ "$TEST_SUITE" != "IntegrationTests" ];
then
    echo "No artifacts for $TEST_SUITE tests.";
    exit;
fi

url="http://builds-artifacts.piwik.org/upload.php?auth_key=$ARTIFACTS_PASS&artifact_name=processed&branch=$TRAVIS_BRANCH&build_id=$TRAVIS_JOB_NUMBER"

echo "Uploading artifacts for $TEST_SUITE..."

cd ./tests/PHPUnit/Integration

# upload processed tarball
tar -cjf processed.tar.bz2 processed --exclude='.gitkeep'
curl -X POST --data-binary @processed.tar.bz2 "$url"

