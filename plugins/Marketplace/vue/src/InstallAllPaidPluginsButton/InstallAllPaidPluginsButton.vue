<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <button
    class="btn"
    @click.prevent="onInstallAllPaidPlugins()"
  >
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
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, MatomoUrl } from 'CoreHome';

export default defineComponent({
  props: {
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true,
    },
    installNonce: {
      type: String,
      required: true,
    },
  },
  methods: {
    onInstallAllPaidPlugins() {
      Matomo.helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce as HTMLElement);
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
