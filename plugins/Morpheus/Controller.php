<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Morpheus;

use Piwik\Development;
use Piwik\Piwik;

class Controller extends \Piwik\Plugin\Controller
{
    public function demo()
    {
        if (! Development::isEnabled() || !Piwik::isUserHasSomeAdminAccess()) {
            return;
        }

        $snippets = [];

        // @phpcs:disable Generic.Files.LineLength
        $snippets[] = [
            'id' => 'ActivityIndicator',
            'title' => 'Loading indicator',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { ActivityIndicator } from \'CoreHome\';

export default defineComponent({
  components: {
    ActivityIndicator,
  },
});
</script>',
            'vue_embed' => '<ActivityIndicator :loading="true"/>',
            'desc' => 'This is a Vue component. You can bind the loading state to a variable if needed.',
            'components' => [
                ['plugin' => 'CoreHome', 'component' => 'ActivityIndicator'],
            ],
        ];

        $snippets[] = [
            'id' => 'Progressbar',
            'title' => 'Progressbar',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { Progressbar } from \'CoreHome\';

export default defineComponent({
  components: {
    Progressbar,
  },
});
</script>',
            'vue_embed' => '<Progressbar :progress="5" label="Downloading database"/>',
            'desc' => 'This is a Vue component. You can bind the progress and label to component state.',
            'components' => [
                ['plugin' => 'CoreHome', 'component' => 'Progressbar'],
            ],
        ];

        // Alerts
        $snippets[] = [
            'id' => 'Alert',
            'title' => 'Alerts',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { Alert } from \'CoreHome\';

export default defineComponent({
  components: {
    Alert,
  },
});
</script>',
            'vue_embed' => '<Alert severity="info">
  <strong>Info!</strong> This alert needs <a>your attention</a>, but it\'s not super important.
</Alert>
<Alert severity="success">
  <strong>Success!</strong> You successfully read this important <a>alert message</a>.
</Alert>
<Alert severity="warning">
  <strong>Warning!</strong> Better <a>check</a> yourself, you\'re not looking too good.
</Alert>
<Alert severity="danger">
  <strong>Error!</strong> Change <a>a few things</a> and try submitting again.
</Alert>',
            'components' => [
                ['plugin' => 'CoreHome', 'component' => 'Alert'],
            ],
        ];

        // Notifications
        $snippets[] = [
            'id' => 'Notification',
            'title' => 'Notifications',
            'code' => '<template>
  %vue_embed%
</template>       
<script lang="ts">
import { defineComponent } from \'vue\';
import { Notification } from \'CoreHome\';

export default defineComponent({
  components: {
    Notification,
  },
});
</script>',
            'vue_embed' => '<Notification context="info">
      <strong>Info</strong>:
      This notification needs <a>your attention</a> but it\'s not super important and has a close button.
    </Notification>
    <Notification context="success">
      <strong>Success</strong>:
      You successfully read this important <a>alert message</a>, it also has a close button.
    </Notification>
    <Notification context="warning" :noclear="true">
      <strong>Warning</strong>:
      Better <a>check</a> yourself, you\'re not looking too good. There is no close button.
    </Notification>
    <Notification context="error" :noclear="true">
      <strong>Error</strong><br>
      Change <a>a few things</a> and try submitting again, this notification does not have a close button, but it does contain multiple lines of text. Textus sine sensu ultra finem lineae pergit ante movens infra et iterum ad sinistram.
    </Notification>',
            'desc' => 'This is a Vue component. You can use the :noclear="true" property to hide the close button',
            'components' => [
                ['plugin' => 'CoreHome', 'component' => 'Notification'],
            ],
        ];


        // forms
        $snippets[] = [
            'title' => 'Forms',
            'vue_embed' => '<p>If you do not want to use one of our form fields we recommend to add the class <code>browser-default</code> to the input or select element.</p>',
        ];

        $snippets[] = $this->formSnippet('simpleField', 'username', "''", '', '<div v-form>
  <Field
    uicontrol="text"
    name="username"
    title="Username"
    introduction="This is an introduction. It can be used to group form fields"
    placeholder="Some text here"
    v-model="username"
  />
</div>', [], false);
        $snippets[] = $this->formSnippet('withInlineHelp', 'email', "''", '', '<div v-form>
  <Field
    uicontrol="email"
    name="email"
    title="Email"
    inline-help="This is the inline help which provides more information."
    v-model="email"
  />
</div>');
        $snippets[] = $this->formSnippet('textWithoutPlaceholder', 'text', "''", '', '<div v-form>
  <Field
    uicontrol="text"
    name="textWithoutPlaceholder"
    title="This field has a title but no place holder"
    v-model="text"
  />
</div>');

        $snippets[] = $this->formSnippet('textWithoutTitle', 'text', "''", '', '<div v-form>
  <Field
    uicontrol="text"
    name="textWithoutTitle"
    placeholder="This field has a place holder but no title"
    v-model="text"
  />
</div>');

        $snippets[] = $this->formSnippet('textWithValue', 'text', "'My value'", 'My value', '<div v-form>
  <Field
    uicontrol="text"
    name="textWithValue"
    v-model="text"
    title="This field has already a value set"
  />
</div>');

        $snippets[] = $this->formSnippet('password', 'pwd', "''", '', '<div v-form>
  <Field
    uicontrol="password"
    name="password"
    title="Password"
    placeholder="Enter your password here"
    v-model="pwd"
  />
</div>');

        $snippets[] = $this->formSnippet('complexHelp', 'text', "''", '', '<div v-form>
  <Field
    uicontrol="text"
    name="alias"
    title="Disabeld text field"
    :disabled="true"
    placeholder="This value cannot be changed"
    v-model="text"
  >
    <template v-slot:inline-help>
      It is possible to use all kind of HTML in the help text, including <a href="" @click.prevent>links</a>.
    </template>
  </Field>
</div>');

        $snippets[] = $this->formSnippet('fullWidthText', 'text', "''", '', '<div v-form>
  <Field
    uicontrol="text"
    name="fullWidthText"
    title="Form fields can be made full width"
    :full-width="true"
    placeholder="Some text here..."
    v-model="text"
  />
</div>');

        $snippets[] = $this->formSnippet('urlText', 'url', "''", '', '<div v-form>
  <Field
    uicontrol="url"
    name="urlText"
    title="URL"
    inline-help="URL field"
    v-model="url"
  />
</div>');

        $snippets[] = $this->formSnippet('textarea', 'description', "''", '', '<div v-form>
  <Field
    uicontrol="textarea"
    name="description"
    title="Description"
    inline-help="This is a textarea. It automatically gets larger the more text is entered."
    v-model="description"
  />
</div>');

        // TODOO: handle arrays
        $snippets[] = $this->formSnippet(
            'language',
            ['language', 'phoneNumber', 'selectedExpand'],
            ['1', '[\'1\']', 'null'],
            [1, ['1'], null],
            '<div v-form>
  <Field
    uicontrol="select"
    name="language"
    title="Language"
    introduction="Select fields"
    inline-help="Single select"
    :options="{1: \'English\', 2: \'Spanish\'}"
    v-model="language"
  />
  <Field
    uicontrol="multiselect"
    name="phonenumbers"
    title="Phone numbers"
    inline-help="Multi select"
    :options="[{key: 1, value: \'0123456789\'},{key: 2, value: \'9876543210\', disabled: true}, {key: 3, value: \'5432109876\'}]"
    v-model="phoneNumber"
  />
  <Field
    uicontrol="expandable-select"
    name="selectexpand"
    :title="selectedExpand ? undefined : \'Select word\'"
    inline-help="Expandable select"
    :options="[{group: \'Group 1\',key:\'1\',value:\'Hello\'}, {group: \'Group 1\',key:\'2\',value:\'How\',tooltip: \'Help text\'}, {group: \'Group 1\',key:\'3\',value:\'Are\'}, {group: \'Group 2\',key:\'4\',value:\'You\'}]"
    v-model="selectedExpand"
  />
</div>'
        );

        $snippets[] = $this->formSnippet('multitupletext', 'values', '[]', [], '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletext"
    title="Multiple values (two)"
    inline-help="Multi Tuple text and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>');

        $snippets[] = $this->formSnippet(
            'multitupletextvalue',
            'values',
            '[{\'index\': \'test\', \'value\':\'myfoo\'},{\'index\': \'test 2\', \'value\':\'myfoo 2\'}]',
            [['index' => 'test', 'value' => 'myfoo'], ['index' => 'test 2', 'value' => 'myfoo 2']],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletextvalue"
    title="Multiple values with values (two)"
    inline-help="Multi Tuple again."
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet(
            'multitupleselect',
            'values',
            '[{\'index\': \'test\', \'value\': \'myfoo\'}]',
            [["index" => "test", "value" => "myfoo"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupleselect"
    title="Multiple values with select (two)"
    inline-help="Multi Tuple select and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'select\',\'availableValues\':{\'test\':\'test\'}},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet('multitupletext2', 'values', '[]', [], '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletext2"
    title="Multiple values (three)"
    inline-help="Multi Tuple text and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>');

        $snippets[] = $this->formSnippet(
            'multitupletextvalue2',
            'values',
            "[{'index': 'test', 'value':'myfoo'},{'index': 'test 2', 'value':'myfoo 2'}]",
            [["index" => "test", "value" => "myfoo"], ["index" => "test 2", "value" => "myfoo 2"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletextvalue2"
    title="Multiple values with values (three)"
    inline-help="Multi Tuple again."
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet(
            'multitupleselect2',
            'values',
            '[{\'index\': \'test\', \'value\': \'myfoo\'}]',
            [['index' => 'test', 'value' => 'myfoo']],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupleselect2"
    title="Multiple values with select (three)"
    inline-help="Multi Tuple select and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'select\',\'availableValues\':{\'test\':\'test\'}},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
    rows="3"
  />
</div>'
        );

        $snippets[] = $this->formSnippet('multitupletext3', 'values', '[]', [], '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletext3"
    title="Multiple values (four)"
    inline-help="Multi Tuple text and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field4\':{\'key\':\'value3\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>');

        $snippets[] = $this->formSnippet('multitupletextfullwidth', 'values', '[]', [], '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletextfullwidth"
    title="Multiple values full width (four)"
    inline-help="Multi Tuple text and text full width"
    :full-width="true"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field4\':{\'key\':\'value3\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>');

        $snippets[] = $this->formSnippet(
            'multitupletextvalue3',
            'values',
            '[{\'index\': \'test\', \'value\':\'myfoo\'},{\'index\': \'test 2\', \'value\':\'myfoo 2\'}]',
            [["index" => "test", "value" => "myfoo"], ["index" => "test 2", "value" => "myfoo 2"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupletextvalue3"
    title="Multiple values with values (four)"
    inline-help="Multi Tuple again."
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field4\':{\'key\':\'value3\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet(
            'multitupleselect3',
            'values',
            '[{\'index\': \'test\', \'value\': \'myfoo\'}]',
            [["index" => "test", "value" => "myfoo"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multitupleselect3"
    title="Multiple values with select (four)"
    inline-help="Multi Tuple select and text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'select\',\'availableValues\':{\'test\':\'test\'}},\'field2\':{\'key\':\'value\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field3\':{\'key\':\'value2\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null},\'field4\':{\'key\':\'value3\',\'title\':\'Value\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet(
            'multituplesingleselect',
            'values',
            '[{\'index\': \'test\', \'value\': \'myfoo\'}]',
            [["index" => "test", "value" => "myfoo"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multituplesingleselect"
    title="Multi One Select"
    inline-help="Multi values with one select"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'select\',\'availableValues\':{\'test\':\'test\'}}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet(
            'multituplesingletext',
            'values',
            '[{\'index\': \'test\', \'value\': \'myfoo\'}]',
            [["index" => "test", "value" => "myfoo"]],
            '<div v-form>
  <Field
    uicontrol="multituple"
    name="multituplesingletext"
    title="Multi One Text"
    inline-help="Multi values with one text"
    :ui-control-attributes="{\'field1\':{\'key\':\'index\',\'title\':\'Index\',\'uiControl\':\'text\',\'availableValues\':null}}"
    v-model="values"
  />
</div>'
        );

        $snippets[] = $this->formSnippet('text-field-array', 'values', "['text one', 'text two']", ['text one', 'text two'], '<div v-form>
  <Field
    uicontrol="field-array"
    name="text-field-array"
    title="Text field array"
    inline-help="Multiple text inputs"
    :ui-control-attributes="{\'field\':{\'title\':\'Index\',\'uiControl\':\'text\'}}"
    v-model="values"
  />
</div>');

        $snippets[] = $this->formSnippet('select-field-array', 'values', "['one', 'two']", ['one', 'two'], '<div v-form>
  <Field
    uicontrol="field-array"
    name="select-field-array"
    title="Select field array"
    inline-help="Multiple selects"
    :ui-control-attributes="{\'field\':{\'title\':\'Index\',\'uiControl\':\'select\',\'availableValues\':{\'one\':\'text one\', \'two\':\'text two\', \'three\':\'text three\'}}}"
    v-model="values"
    rows="5"
  />
</div>');

        $snippets[] = $this->formSnippet(
            'enableFeatures',
            ['enable', 'enableArray', 'defaultReportDate'],
            ['false', '[]', 'null'],
            [false, [], null],
            '<div v-form>
  <Field
    uicontrol="checkbox"
    name="enableFeature"
    title="Enable feature"
    introduction="Radio and checkboxes"
    inline-help="This is a single checkbox"
    v-model="enable"
  />
  <Field
    uicontrol="checkbox"
    name="enableFeature"
    title="Enable feature"
    var-type="array"
    :options=\'[{"name":"enableFeatures[]", "key":"today", "value":"Today"},{"name":"enableFeatures[]", "key":"yesterday", "value":"Yesterday"},{"name":"enableFeatures[]", "key":"week", "value":"Previous 30 days (not including today)"}]\'
    inline-help="This field shows multiple checkboxes as we declare we want to get an array of values."
    v-model="enableArray"
  />
  <Field
    uicontrol="radio"
    name="defaultReportDate"
    title="Report to load by default"
    :options="{today: \'Today\', yesterday: \'Yesterday\',week: \'Previous 30 days (not including today)\'}"
    inline-help="This is a help text that can be used to describe the field. This help text may extend over several lines."
    v-model="defaultReportDate"
  />
</div>'
        );

        $snippets[] = $this->formSnippet('currentsite', ['site', 'isDisabled', 'saveCount', 'isLoading'], ['null', 'false', '0', 'false'], [null, false, 0, false], '<div v-form>
  <Field
    uicontrol="site"
    name="currentsite"
    introduction="Matomo specific form fields"
    title="Select a website"
    v-model="site"
  />
  <SaveButton @confirm="isDisabled = !isDisabled" style="margin-right: 3.5px" />
  <SaveButton
    @confirm="saveCount += 1"
    :disabled="isDisabled"
    value="Changed button text"
    :saving="isLoading"
  />
  <p>The second save button was clicked <span v-text="saveCount"></span> times.</p>
</div>', [['plugin' => 'CorePluginsAdmin', 'component' => 'SaveButton']]);

        $snippets[] = [
            'id' => 'inlinecode',
            'title' => 'Inline Code',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { SelectOnFocus } from \'CoreHome\';

export default defineComponent({
  directives: {
    SelectOnFocus,
  },
});
</script>',
            'vue_embed' => '<p>
  You can put code in a text using the <div><code v-select-on-focus="{}">code</code></div> tag.
</p>',
            'directives' => [
                ['plugin' => 'CoreHome', 'directive' => 'SelectOnFocus'],
            ],
        ];

        $snippets[] = [
            'id' => 'blockcode',
            'title' => 'Block Code',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { CopyToClipboard } from \'CoreHome\';

export default defineComponent({
  directives: {
    CopyToClipboard,
  },
});
</script>',
            'vue_embed' => '<p>Or you can display a code block:</p>
<div><pre v-copy-to-clipboard="{}">&lt;!-- Matomo --&gt;
&lt;script type=&quot;text/javascript&quot;&gt;
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;</pre></div>',
            'directives' => [
                ['plugin' => 'CoreHome', 'directive' => 'CopyToClipboard'],
            ],
        ];

        $snippets[] = [
            'id' => 'tables',
            'title' => 'Tables',
            'code' => '%vue_embed%',
            'vue_embed' => '<table>
<thead>
<tr>
  <th>First Name</th>
  <th>Last Name</th>
  <th>Username</th>
</tr>
</thead>
<tbody>
<tr>
  <td>Mark</td>
  <td>Otto</td>
  <td>@mdo</td>
</tr>
<tr>
  <td>Jacob</td>
  <td>Thornton</td>
  <td>@fat</td>
</tr>
<tr>
  <td>Larry</td>
  <td>the Bird</td>
  <td>@twitter</td>
</tr>
</tbody>
</table>',
        ];

        $snippets[] = [
            'id' => 'contentintro',
            'title' => 'Content intro',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { ContentIntro } from \'CoreHome\';

export default defineComponent({
  directives: {
    ContentIntro,
  },
});
</script>',
            'vue_embed' => '<div v-content-intro>
  <h2>My headline</h2>
  <p>My text goes is in here</p>
</div>',
            'directives' => [['plugin' => 'CoreHome', 'directive' => 'ContentIntro']],
            'desc' => 'A content intro can be used as an introduction to the following content and is usually used as the first part of a page followed by one or multiple content blocks.',
        ];

        $snippets[] = [
            'id' => 'contentblock',
            'title' => 'Content blocks',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { ContentBlock } from \'CoreHome\';

export default defineComponent({
  components: {
    ContentBlock,
  },
});
</script>',
            'components' => [['plugin' => 'CoreHome', 'component' => 'ContentBlock']],
            'vue_embed' => '<ContentBlock content-title="My title" help-url="https://matomo.org">
  <p>My text goes is in here</p>
</ContentBlock>',
        ];

        $snippets[] = [
            'id' => 'contenttable',
            'title' => 'Content table',
            'code' => '<template>
  %vue_embed%
</template>

<script lang="ts">
import { defineComponent } from \'vue\';
import { ContentBlock, ContentTable } from \'CoreHome\';

export default defineComponent({
  components: {
    ContentBlock,
  },
  directives: {
      ContentTable,
  },
});
</script>',
            'vue_embed' => '<ContentBlock content-title="My title" help-url="https://matomo.org">
  <p>My intro text is here</p>
  <table v-content-table>
    <thead>
      <tr><th>Column 1</th><th>Column 2</th></tr>
    </thead>
    <tbody>
      <tr><td>Value 1</td><td>Value 2</td></tr>
      <tr><td>Value 1</td><td>Value 2</td></tr>
    </tbody>
  </table>
</ContentBlock>',
            'components' => [['plugin' => 'CoreHome', 'component' => 'ContentBlock']],
            'directives' => [['plugin' => 'CoreHome', 'directive' => 'ContentTable']],
        ];

        $icons = [
            'Manage' => [
                'add',
                'edit',
                'delete',
                'plus',
                'minus',
                'archive',
                'add1',
                'remove'
            ],
            'Alerts' => [
                'error',
                'warning',
                'info',
                'success',
                'help',
                'ok'
            ],
            'Navigation' => [
                'arrow-left',
                'arrow-right',
                'arrow-left-2',
                'arrow-right-2',
                'arrow-top',
                'arrow-bottom',
                'zoom-in',
                'zoom-out',
                'show',
                'hide',
                'search',
                'menu-hamburger',
                'more-horiz',
                'more-verti',
                'arrowup',
                'arrowdown',
                'chevron-right',
                'chevron-left',
                'chevron-down',
                'chevron-up',
            ],
            'Window-Widget' => [
                'minimise',
                'fullscreen',
                'close',
                'maximise',
                'refresh',
                'reload'
            ],
            'Reports' => [
                'table',
                'table-more',
                'chart-bar',
                'chart-pie',
                'evolution',
                'funnel',
                'form',
                'transition',
                'overlay',
                'lab',
                'clock'
            ],
            'Users' => [
                'user',
                'user-add',
                'users',
                'user-personal'
            ],
            'Date-picker' => [
                'calendar',
                'datepicker-arr-l',
                'datepicker-arr-r'
            ],
            'Annotations' => [
                'annotation'
            ],
            'E-commerce' => [
                'ecommerce-order',
                'ecommerce-abandoned-cart'
            ],
            'Goals' => [
                'goal'
            ],
            'Insights' => [
                'insights'
            ],
            'Segments' => [
                'segment'
            ],
            'Visitors' => [
                'visitor-profile',
                'segmented-visits-log'
            ],
            'Lock' => [
                'locked'
            ],
            'Media' => [
                'audio',
                'play',
                'pause',
                'replay',
                'stop',
                'fast-forward',
                'fast-rewind',
                'skip-next',
                'skip-previous'
            ],
            'Other' => [
                'configure',
                'document',
                'email',
                'export',
                'feed',
                'download',
                'image',
                'code',
                'star',
                'drop',
                'drop-crossed',
                'business',
                'finance',
                'folder',
                'github',
                'open-source',
                'puzzle',
                'server',
                'tag-cloud',
                'sign-in',
                'sign-out',
                'settings',
                'rocket',
                'bug',
                'upload',
                'embed',
                'heart',
                'merge',
                'content-copy',
                'new_releases',
                'notifications_on',
                'reporting-dashboard',
                'reporting-actions',
                'reporting-visitors',
                'reporting-referer',
                'admin-diagnostic',
                'admin-platform',
                'admin-development',
                'admin-settings',
                'marketplace',
                'plugin'
            ],
        ];
        // @phpcs:enable Generic.Files.LineLength

        return $this->renderTemplate('demo', [
            'snippets' => $snippets,
            'icons' => $icons,
        ]);
    }

    private function formSnippet($id, $dataName, $dataValueCode, $dataValue, $demoCode, $extraComponents = [], $noMargin = true)
    {
        if (is_array($dataName)) {
            $dataCode = "";
            foreach ($dataName as $idx => $name) {
                $dataCode .= "      $name: " . $dataValueCode[$idx] . ",\n";
            }
        } else {
            $dataCode = "      $dataName: $dataValueCode,\n";
        }

        if (is_array($dataName)) {
            $data = [];
            foreach ($dataName as $idx => $name) {
                $data[$name] = $dataValue[$idx];
            }
        } else {
            $data = [$dataName => $dataValue];
        }

        return [
            'id' => "form.$id",
            'code' => "<template>
  %vue_embed%
</template>

<script lang=\"ts\">
import { defineComponent } from 'vue';
import { Field, Form } from 'CorePluginsAdmin';

export default defineComponent({
  components: {
    Field,
  },
  directives: {
    Form,
  },
  data() {
    return {
$dataCode    };
  },
});
</script>",
            'vue_embed' => $demoCode,
            'components' => array_merge([
                ['plugin' => 'CorePluginsAdmin', 'component' => 'Field'],
            ], $extraComponents),
            'directives' => [
                ['plugin' => 'CorePluginsAdmin', 'directive' => 'Form'],
            ],
            'data' => $data,
            'noMargin' => $noMargin,
        ];
    }
}
