<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

/**
 * TODO
 */
interface GlobalSettingsProvider
{
    /**
     * TODO
     */
    public function &getSection($name);

    /**
     * TODO
     */
    public function setSection($name, $value);
}