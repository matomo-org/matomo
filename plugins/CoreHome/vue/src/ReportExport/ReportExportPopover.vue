<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="report-export-popover row">

    <div class="col l6">
      <div>
        <Field
          :uicontrol="'radio'"
          :name="'format'"
          :title="translate('CoreHome_ExportFormat')"
          v-model="reportFormat"
          :full-width="true"
          :options="availableReportFormats[reportType]"
        />
      </div>

      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_flat'"
          :title="translate('CoreHome_FlattenReport')"
          v-model="optionFlat"
          v-show="hasSubtables"
        >
        </Field>
      </div>
      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_expanded'"
          :title="translate('CoreHome_ExpandSubtables')"
          v-model="optionExpanded"
          v-show="hasSubtables && !optionFlat"
        >
        </Field>
      </div>
      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_format_metrics'"
          :title="translate('CoreHome_FormatMetrics')"
          v-model="optionFormatMetrics"
        >
        </Field>
      </div>
    </div>

    <div class="col l6">
      <div>
        <Field
          :uicontrol="'radio'"
          :name="'filter_type'"
          :title="translate('CoreHome_ReportType')"
          v-model="reportType"
          :full-width="true"
          :options="availableReportTypes"
        >
        </Field>
      </div>

      <div class="filter_limit">
        <div>
          <Field
            :uicontrol="'radio'"
            :name="'filter_limit_all'"
            :title="translate('CoreHome_RowLimit')"
            v-model="reportLimitAll"
            v-hide="maxFilterLimit > 0"
            :full-width="true"
            :options="limitAllOptions"
          >
          </Field>
        </div>

        <div>
          <Field
            :uicontrol="'number'"
            name="filter_limit"
            :min="1"
            v-model="reportLimit"
            :full-width="true"
            v-if="reportLimitAll === 'no' && maxFilterLimit <= 0"
          >
          </Field>
        </div>

        <div>
          <Field
            :uicontrol="'number'"
            :name="'filter_limit'"
            :min="1"
            :max="maxFilterLimit"
            v-model="reportLimit"
            :value="reportLimit"
            @update:model-value="checkNumberForLimit"
            :full-width="true"
            :title="`${translate('CoreHome_RowLimit')} (${translate('General_ComputedMetricMax', maxFilterLimit)})`"
            v-if="reportLimitAll === 'no' && maxFilterLimit > 0">
          </Field>
        </div>
      </div>
    </div>

    <div class="col l12" v-show="showUrl">
      <textarea v-select-on-focus="{}" readonly class="exportFullUrl">
        {{ exportLinkWithoutToken }}
      </textarea>
      <div class="tooltip" v-html="$sanitize(translate(
        'CoreHome_ExportTooltipWithLink',
        '<a target=_blank href=\'?module=UsersManager&action=userSecurity\'>',
        '</a>',
        'ENTER_YOUR_TOKEN_AUTH_HERE',
      ))"></div>
    </div>

    <div class="col l12">
      <a
        class="btn"
        :href="exportLink"
        target="_new"
        :title="translate('CoreHome_ExportTooltip')"
      >{{ translate('General_Export') }}</a>
      <a href="javascript:" @click="showUrl=!showUrl" class="toggle-export-url">
        <span v-show="!showUrl">{{ translate('CoreHome_ShowExportUrl') }}</span>
        <span v-show="showUrl">{{ translate('CoreHome_HideExportUrl') }}</span>
      </a>
    </div>

  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import SelectOnFocus from '../SelectOnFocus/SelectOnFocus';

const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

export default defineComponent({
  components: {
    Field,
  },
  directives: {
    SelectOnFocus,
  },
  props: {
    hasSubtables: Boolean,
    availableReportTypes: Array,
    availableReportFormats: Object,
    maxFilterLimit: Number,
    limitAllOptions: Array,
    // TODO
  },
  data() {
    return {
      reportFormat: 'XML',
      optionFlat: false,
      optionExpanded: false,
      optionFormatMetrics: false,
      reportType: '',
      reportLimitAll: false,
      reportLimit: 100,
    };
  },
  computed: {
    exportLink() {
      // TODO
    },
    exportLinkWithoutToken() {
      // TODO
    },
  },
  methods: {
    checkNumberForLimit() {
      // TODO
    },
  }
});
</script>
