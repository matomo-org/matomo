<?php

namespace Piwik\Plugins\CoreUpdater\ReleaseChannel;

use Piwik\UpdateCheck\ReleaseChannel;
use Piwik\Url;
use Piwik\Version;

class GitCommitReleaseChannel extends ReleaseChannel
{
    public function getId()
    {
        return 'git_commit';
    }

    public function getName()
    {
        return 'Test Release Channel';
    }

    public function getUrlToCheckForLatestAvailableVersion()
    {
        $majorVersion = (int) Version::VERSION;
        $majorVersion += 1;

        return 'http://' . Url::getHost(false) . '/tests/resources/one-click-update-version.php?v=' . $majorVersion;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        return '://' . Url::getHost(false) . '/matomo-build.zip';
    }
}
