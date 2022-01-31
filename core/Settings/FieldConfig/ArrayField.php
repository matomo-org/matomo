<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\FieldConfig;

/**
 * Lets you configure a field for a field array.
 *
 * Usage:
 *
 * $field->uiControl = FieldConfig::UI_CONTROL_FIELD_ARRAY;
 * $arrayField = new FieldConfig\ArrayField('Index', FieldConfig::UI_CONTROL_TEXT);
 * $field->uiControlAttributes['field'] = $field->toArray();
 *
 * @api
 */
class ArrayField
{
    /**
     * Describes what HTML element should be used to manipulate the setting through Piwik's UI.
     *
     * See {@link Piwik\Plugin\Settings} for a list of supported control types.
     *
     * @var string
     */
    public $uiControl = null;

    /**
     * Defines a custom template file for a UI control. This file should render a UI control and expose the value in a
     * "formField.value" angular model. For an example see "plugins/CorePluginsAdmin/angularjs/form-field/field-text.html"
     *
     * @var string
     */
    public $customUiControlTemplateFile = '';

    /**
     * Array like ['plugin' => 'MyPlugin', 'component' => 'MyExportedCustomFieldComponent']. For an example see
     * "plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue"
     *
     * @var string[]
     */
    public $customFieldComponent = null;

    /**
     * This setting's display name, for example, `'Refresh Interval'`.
     *
     * Be sure to escape any user input as HTML can be used here.
     *
     * @var string
     */
    public $title = '';

    /**
     * The list of all available values for this setting. If null, the setting can have any value.
     *
     * If supplied, this field should be an array mapping available values with their prettified
     * display value. Eg, if set to `array('nb_visits' => 'Visits', 'nb_actions' => 'Actions')`,
     * the UI will display **Visits** and **Actions**, and when the user selects one, Piwik will
     * set the setting to **nb_visits** or **nb_actions** respectively.
     *
     * @var null|array
     */
    public $availableValues = null;

    public function __construct($title, $uiControl = 'text')
    {
        $this->title = $title;
        $this->uiControl = $uiControl;
    }

    public function toArray()
    {
        return array(
            'title' => $this->title,
            'uiControl' => $this->uiControl,
            'templateFile' => $this->customUiControlTemplateFile,
            'component' => $this->customFieldComponent,
            'availableValues' => $this->availableValues,
        );
    }

}
