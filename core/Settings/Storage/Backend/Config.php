<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

/**
 * Backend for an existing site. Stores all settings in the "site" database table.
 */
class Config implements BackendInterface
{
    private $section;

    public function __construct($section)
    {
        if (empty($section)) {
            throw new \Exception('No section given for config section backend');
        }

        $this->section = $section;
    }

    /**
     * @inheritdoc
     */
    public function getStorageId()
    {
        return 'Config_' . $this->section;
    }

    private function getConfig()
    {
        return \Piwik\Config::getInstance();
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        $section = $this->load();

        foreach ($values as $key => $value) {
            $section[$key] = $value;
        }

        $config = $this->getConfig();
        $config->{$this->section} = $section;
        $config->forceSave();
    }

    public function load()
    {
        $config  = $this->getConfig();
        $section = $config->{$this->section};

        $values = array();
        // remove reference
        foreach ($section as $key => $value) {
            $values[$key] = $value;
        }

        return $values;
    }

    public function delete()
    {
        $this->getConfig()->{$this->section} = array();
    }

}
