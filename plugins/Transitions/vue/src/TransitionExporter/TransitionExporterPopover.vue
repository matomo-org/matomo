<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transition-export-popover row">
    <div class="col l6">
      <div class="input-field">
        <div class="matomo-field">
          <Field
            uicontrol="radio"
            name="exportFormat"
            :title="translate('CoreHome_ExportFormat')"
            :model-value="exportFormat"
            @update:model-value="exportFormat = $event"
            :full-width="true"
            :options="exportFormatOptions"
          />
        </div>
      </div>
    </div>

    <div class="col l12">
      <a
        class="btn"
        :href="exportLink"
        target="_new"
        title="translate('CoreHome_ExportTooltip')"
      >
        {{ translate('General_Export') }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, MatomoUrl } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import { actionType, actionName } from './transitionParams';

interface TransitionExportPopoverState {
  exportFormat: string;
}

export default defineComponent({
  props: {
    exportFormatOptions: {
      type: Object,
      required: true,
    },
  },
  components: {
    Field,
  },
  data(): TransitionExportPopoverState {
    return {
      exportFormat: 'JSON',
    };
  },
  computed: {
    exportLink() {
      const exportUrlParams: QueryParameters = {
        module: 'API',
      };

      exportUrlParams.method = 'Transitions.getTransitionsForAction';
      exportUrlParams.actionType = actionType.value;
      exportUrlParams.actionName = actionName.value;

      exportUrlParams.idSite = Matomo.idSite;
      exportUrlParams.period = Matomo.period;
      exportUrlParams.date = Matomo.currentDateString;
      exportUrlParams.format = this.exportFormat;
      exportUrlParams.token_auth = Matomo.token_auth;
      exportUrlParams.force_api_session = 1;

      const currentUrl = window.location.href;

      const urlParts = currentUrl.split('/');
      urlParts.pop();

      const url = urlParts.join('/');
      return `${url}/index.php?${MatomoUrl.stringify(exportUrlParams)}`;
    },
  },
});
</script>
