<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\UpdateCheck;

/**
 * Base class to define a custom release channel. Plugins can add their own custom release channels by extending this
 * class in a `plugin/$plugin/ReleaseChannel` folder. Custom release channels can be useful for example to provide
 * nightly builds, to manage updates for clients via a central server, to package a special Piwik version for clients
 * with custom plugins etc.
 *
 * This is not a public API and it may change without any announcement.
 */
abstract class ReleaseChannel
{
    /**
     * Get the ID for this release channel. This string will be eg saved in the config to identify the chosen release
     * channel
     * @return string
     */
    abstract public function getId();

    /**
     * Get a human readable name for this release channel, will be visible in the UI. Should be already translated.
     * @return string
     */
    abstract public function getName();

    /**
     * Whether only stable versions are wanted or also beta versions.
     * @return bool
     */
    public function doesPreferStable()
    {
        return true;
    }

    /**
     * Get the latest available version number for this release channel. Eg '2.15.0-b4' or '2.15.0'. Should be
     * a semantic version number in format MAJOR.MINOR.PATCH (http://semver.org/). Returning an empty string in case
     * one cannot connect to the remote server can be acceptable.
     * @return string
     */
    abstract public function getUrlToCheckForLatestAvailableVersion();

    /**
     * Get the URL to download a specific Piwik archive for the given version number. The returned URL should not
     * include a URI scheme, meaning it should start with '://...'.
     *
     * @param string $version
     * @return string
     */
    abstract public function getDownloadUrlWithoutScheme($version);

    /**
     * Get the description for this release channel. Will be shown directly next to the name of the release in the
     * Admin UI. For example 'Recommended' or 'Long Term Support version'.
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Get the order for this release channel. The lower the number the more important this release channel is. The
     * release channel having the lowest order will be shown first and will be used as default release channel in case
     * no valid release channel is defined.
     * @return int
     */
    public function getOrder()
    {
        return 99;
    }
}