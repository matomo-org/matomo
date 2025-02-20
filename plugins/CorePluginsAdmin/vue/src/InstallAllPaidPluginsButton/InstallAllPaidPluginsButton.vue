<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="paidPluginsToInstallAtOnce.length">
    <button
      class="btn installAllPaidPluginsAtOnceButton"
      @click.prevent="onInstallAllPaidPlugins()"
      :disabled="disabled || loading"
    >
      <MatomoLoader v-if="loading"/>
      {{ translate('Marketplace_InstallPurchasedPlugins') }}
    </button>
    <div
      class="ui-confirm"
      id="installAllPaidPluginsAtOnce"
      ref="installAllPaidPluginsAtOnce"
    >
      <h2>{{ translate('Marketplace_InstallAllPurchasedPlugins') }}</h2>
      <p>
        {{ translate('Marketplace_InstallThesePlugins') }}
      </p>
      <ul>
        <li v-for="pluginDisplayName in paidPluginsToInstallAtOnce" :key="pluginDisplayName">
          {{ pluginDisplayName }}
        </li>
      </ul>
      <p>
        <input
          role="install"
          type="button"
          :data-href="installAllPaidPluginsLink"
          :value="translate(
                  'Marketplace_InstallAllPurchasedPluginsAction',
                  paidPluginsToInstallAtOnce.length,
                )"
        />
        <input
          role="cancel"
          type="button"
          :value="translate('General_Cancel')"
        />
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo, MatomoUrl, MatomoLoader, AjaxHelper,
} from 'CoreHome';

interface installAllPaidPluginsButton {
  paidPluginsToInstallAtOnce: Array<string>;
  installNonce: string;
  loading: boolean;
}
export default defineComponent({
  components: { MatomoLoader },
  props: {
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data(): installAllPaidPluginsButton {
    return {
      paidPluginsToInstallAtOnce: ([]) as Array<string>,
      installNonce: '',
      loading: false,
    };
  },
  created() {
    this.fetchPluginsToInstallAtOnce();
  },
  watch: {
    disabled(newValue: boolean, oldValue: boolean) {
      if (newValue === false && oldValue === true) {
        this.fetchPluginsToInstallAtOnce();
      }
    },
  },
  methods: {
    onInstallAllPaidPlugins() {
      Matomo.helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce as HTMLElement);
    },
    fetchPluginsToInstallAtOnce() {
      this.loading = true;
      if (Matomo.hasSuperUserAccess) {
        AjaxHelper.fetch({
          module: 'Marketplace',
          action: 'getPaidPluginsToInstallAtOnceParams',
        }).then((response) => {
          if (response) {
            this.paidPluginsToInstallAtOnce = response.paidPluginsToInstallAtOnce ?? [];
            this.installNonce = response.installAllPluginsNonce ?? '';
          }
          this.loading = false;
        });
      }
    },
  },
  computed: {
    installAllPaidPluginsLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'installAllPaidPlugins',
        nonce: this.installNonce,
      })}`;
    },
  },
});
</script>
