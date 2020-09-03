<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;

/**
 * Used to manipulate Environment instances before the container is created.
 * Only used by the testing environment setup code, shouldn't be used anywhere
 * else.
 */
interface EnvironmentManipulator
{
    /**
     * Create a custom GlobalSettingsProvider kernel object, overriding the default behavior.
     *
     * @return GlobalSettingsProvider
     */
    public function makeGlobalSettingsProvider(GlobalSettingsProvider $original);

    /**
     * Create a custom PluginList kernel object, overriding the default behavior.
     *
     * @param GlobalSettingsProvider $globalSettingsProvider
     * @return PluginList
     */
    public function makePluginList(GlobalSettingsProvider $globalSettingsProvider);

    /**
     * Invoked before the container is created.
     */
    public function beforeContainerCreated();

    /**
     * Return an array of definition arrays that override DI config specified in PHP config files.
     *
     * @return array[]
     */
    public function getExtraDefinitions();

    /**
     * Invoked after the container is created and the environment is considered bootstrapped.
     */
    public function onEnvironmentBootstrapped();

    /**
     * Return an array of environment names to apply after the normal environment.
     *
     * @return string[]
     */
    public function getExtraEnvironments();
}
