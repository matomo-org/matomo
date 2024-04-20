<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <p>
    {{ translate('Installation_CopyBelowInfoForSupport') }}
    <br/> <br/>
    <a href=""
       @click.prevent="copyInfo()"
       class='btn'
       style="margin-right:3.5px"
    >{{ translate('Installation_CopySystemCheck') }}</a>
    <a href=""
       @click.prevent="downloadInfo()"
       class='btn'
    >{{ translate('Installation_DownloadSystemCheck') }}</a>
  </p>

  <div>
    <textarea
      style="width:100%;height: 200px;"
      readonly
      id="matomo_system_check_info"
      ref="systemCheckInfo"
      v-html="$sanitize(systemCheckInfo)"
    ></textarea>

    <table
      class="entityTable system-check"
      id="systemCheckRequired"
      v-content-table="{off: isInstallation}"
    >
      <tbody>
        <DiagnosticTable
          :results="mandatoryResults"
          :informational-type="informationalType"
          :warning-type="warningType"
          :error-type="errorType"
        />
      </tbody>
    </table>

    <h3>{{ translate('Installation_Optional') }}</h3>

    <table
      class="entityTable system-check"
      id="systemCheckOptional"
      v-content-table="{off: isInstallation}"
    >
      <tbody>
        <DiagnosticTable
          :results="optionalResults"
          :informational-type="informationalType"
          :warning-type="warningType"
          :error-type="errorType"
        />
      </tbody>
    </table>

    <h3>{{ translate('Installation_InformationalResults') }}</h3>

    <table
      class="entityTable system-check"
      id="systemCheckInformational"
      v-content-table="{off: isInstallation}"
    >
      <tbody>
        <DiagnosticTable
          :results="informationalResults"
          :informational-type="informationalType"
          :warning-type="warningType"
          :error-type="errorType"
        />
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, ContentTable } from 'CoreHome';
import DiagnosticTable from './DiagnosticTable.vue';

const { $ } = window;

export default defineComponent({
  props: {
    errorType: {
      type: String,
      required: true,
    },
    warningType: {
      type: String,
      required: true,
    },
    informationalType: {
      type: String,
      required: true,
    },
    systemCheckInfo: {
      type: String,
      required: true,
    },
    mandatoryResults: {
      type: Array,
      required: true,
    },
    optionalResults: {
      type: Array,
      required: true,
    },
    informationalResults: {
      type: Array,
      required: true,
    },
    isInstallation: Boolean,
  },
  components: {
    DiagnosticTable,
  },
  directives: {
    ContentTable,
  },
  methods: {
    copyInfo() {
      const textarea = this.$refs.systemCheckInfo as HTMLTextAreaElement;
      textarea.select();
      document.execCommand('copy');

      $(textarea).effect('highlight', {}, 600);
    },
    downloadInfo() {
      const textarea = this.$refs.systemCheckInfo as HTMLTextAreaElement;
      Matomo.helper.sendContentAsDownload('matomo_system_check.txt', textarea.innerHTML);
    },
  },
});
</script>
