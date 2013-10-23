<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Settings;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Settings;

/**
 * Settings manager
 *
 * @package Piwik
 * @subpackage Settings
 */
abstract class Setting
{
    public $type            = Settings::TYPE_STRING;
    public $field           = Settings::FIELD_TEXT;
    public $fieldAttributes = array();
    public $fieldOptions    = null;
    public $introduction    = null;
    public $description     = null;
    public $inlineHelp      = null;
    public $filter          = null;
    public $validate        = null;
    public $defaultValue    = null;
    public $title           = '';

    protected $key;
    protected $name;
    protected $displayedForCurrentUser = false;

    public function canBeDisplayedForCurrentUser()
    {
        return $this->displayedForCurrentUser;
    }

    public function __construct($name, $title)
    {
        if (!ctype_alnum($name)) {
            // TODO escape name?
            $msg = sprintf('The setting name %s is not valid. Only alpha and numerical characters are allowed', $name);
            throw new \Exception($msg);
        }

        $this->key   = $name;
        $this->name  = $name;
        $this->title = $title;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the key under which property name the setting will be stored.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function hasKey($key)
    {
        return $key == $this->getKey();
    }

    public function getDefaultType($field)
    {
        $defaultTypes = array(
            Settings::FIELD_TEXT          => Settings::TYPE_STRING,
            Settings::FIELD_TEXTAREA      => Settings::TYPE_STRING,
            Settings::FIELD_PASSWORD      => Settings::TYPE_STRING,
            Settings::FIELD_CHECKBOX      => Settings::TYPE_BOOL,
            Settings::FIELD_MULTI_SELECT  => Settings::TYPE_ARRAY,
            Settings::FIELD_SINGLE_SELECT => Settings::TYPE_STRING,
        );

        return $defaultTypes[$field];
    }

    public function getDefaultField($type)
    {
        $defaultFields = array(
            Settings::TYPE_INT    => Settings::FIELD_TEXT,
            Settings::TYPE_FLOAT  => Settings::FIELD_TEXT,
            Settings::TYPE_STRING => Settings::FIELD_TEXT,
            Settings::TYPE_BOOL   => Settings::FIELD_CHECKBOX,
            Settings::TYPE_ARRAY  => Settings::FIELD_MULTI_SELECT,
        );

        return $defaultFields[$type];
    }
}
