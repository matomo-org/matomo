<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Measurable;

use Piwik\Piwik;

/**
 * Describes a Type setting for a website, mobile app, ...
 *
 * See {@link \Piwik\Plugin\Settings}.
 */
class MeasurableSetting extends \Piwik\Settings\Setting
{
    /**
     * By default the value of the type setting is only readable by users having at least view access to one site
     *
     * @var bool
     * @since 2.14.0
     */
    public $readableByCurrentUser = false;

    /**
     * By default the value of the type setting is only writable by users having at least admin access to one site
     * @var bool
     * @internal
     */
    public $writableByCurrentUser = false;

    /**
     * Constructor.
     *
     * @param string $name The persisted name of the setting.
     * @param string $title The display name of the setting.
     */
    public function __construct($name, $title)
    {
        parent::__construct($name, $title);

        $this->writableByCurrentUser = Piwik::isUserHasSomeAdminAccess();
        $this->readableByCurrentUser = Piwik::isUserHasSomeViewAccess();
    }

    /**
     * Returns `true` if this setting is writable for the current user, `false` if otherwise. In case it returns
     * writable for the current user it will be visible in the Plugin settings UI.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        return $this->writableByCurrentUser;
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isReadableByCurrentUser()
    {
        return $this->readableByCurrentUser;
    }
}
