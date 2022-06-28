<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <p>
    {{ translate('Installation_CopyBelowInfoForSupport') }}
    <br/> <br/>
    <a href=""
       @click.prevent="copyInfo()"
       class='btn'
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
      :value="systemCheckInfo"
    ></textarea>

    <table class="entityTable system-check" id="systemCheckRequired" {% if isInstallation is not defined %}piwik-content-table{% endif %}>
      {{ local.diagnosticTable(diagnosticReport.getMandatoryDiagnosticResults()) }}
    </table>

    <h3>{{ translate('Installation_Optional') }}</h3>

    <table class="entityTable system-check" id="systemCheckOptional" {% if isInstallation is not defined %}piwik-content-table{% endif %}>
      {{ local.diagnosticTable(diagnosticReport.getOptionalDiagnosticResults()) }}
    </table>

    <h3>{{ translate('Installation_InformationalResults') }}</h3>

    <table class="entityTable system-check" id="systemCheckInformational" {% if isInstallation is not defined %}piwik-content-table{% endif %}>
      {{ local.diagnosticTable(diagnosticReport.getInformationalResults()) }}
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, Matomo } from 'CoreHome';

const { $ } = window;

export default defineComponent({
  props: {
    errorType: {
      type: Number,
      required: true,
    },
    warningType: {
      type: Number,
      required: true,
    },
    informationalType: {
      type: Number,
      required: true,
    },
    systemCheckInfo: {
      type: String,
      required: true,
    },
    // TODO
  },
  components: {
    ContentBlock,
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
