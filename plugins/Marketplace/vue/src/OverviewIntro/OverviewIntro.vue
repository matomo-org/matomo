<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline
        :feature-name="translate('CorePluginsAdmin_Marketplace')"
      >
        {{ translate('Marketplace_Marketplace') }}
      </EnrichedHeadline>
    </h2>

    <div class="marketplaceIntro">
      <p v-if="!isSuperUser">
          {{ translate('Marketplace_Intro') }}
      </p>
      <p v-else>
          {{ translate('Marketplace_IntroSuperUser') }}
      </p>
    </div>

    <div class="installAllPaidPlugins" v-if="installAllPaidPluginsVisible">
      <InstallAllPaidPluginsButton
        :disabled="installDisabled"
      />
    </div>

    <Marketplace
      :plugin-type-options="pluginTypeOptions"
      :default-sort="defaultSort"
      :plugin-sort-options="pluginSortOptions"
      :num-available-plugins-by-type="numAvailablePluginsByType"
      :current-user-email="currentUserEmail"
      :is-auto-update-possible="isAutoUpdatePossible"
      :is-super-user="isSuperUser"
      :is-multi-server-environment="isMultiServerEnvironment"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :is-valid-consumer="getIsValidConsumer"
      :deactivate-nonce="deactivateNonce"
      :activate-nonce="activateNonce"
      :install-nonce="installNonce"
      :update-nonce="updateNonce"
      :has-some-admin-access="hasSomeAdminAccess"
      :num-users="numUsers"
      @triggerUpdate="this.updateOverviewData()"
      @startTrialStart="this.disableInstallAllPlugins(true)"
      @startTrialStop="this.disableInstallAllPlugins(false)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentIntro, EnrichedHeadline, MatomoUrl,
} from 'CoreHome';
import { InstallAllPaidPluginsButton } from 'CorePluginsAdmin';
import Marketplace from '../Marketplace/Marketplace.vue';

import { TObject } from '../types';

interface OverviewIntroState {
  updating: boolean;
  fetchRequest: Promise<void>|null;
  fetchRequestAbortController: AbortController|null;
  updateData: TObject|null,
  installDisabled: boolean;
  installLoading: boolean;
}

export default defineComponent({
  props: {
    currentUserEmail: String,
    inReportingMenu: Boolean,
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    hasSomeAdminAccess: Boolean,
    installNonce: {
      type: String,
      required: true,
    },
    activateNonce: {
      type: String,
      required: true,
    },
    deactivateNonce: {
      type: String,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    pluginTypeOptions: {
      type: Object,
      required: true,
    },
    defaultSort: {
      type: String,
      required: true,
    },
    pluginSortOptions: {
      type: Object,
      required: true,
    },
    numAvailablePluginsByType: {
      type: Object,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
  },
  components: {
    InstallAllPaidPluginsButton,
    EnrichedHeadline,
    Marketplace,
  },
  directives: {
    ContentIntro,
  },
  data(): OverviewIntroState {
    return {
      updating: false,
      fetchRequest: null,
      fetchRequestAbortController: null,
      updateData: null,
      installDisabled: false,
      installLoading: false,
    };
  },
  computed: {
    getIsValidConsumer(): boolean {
      return (this.updateData && typeof this.updateData.isValidConsumer !== 'undefined'
        ? this.updateData.isValidConsumer
        : this.isValidConsumer) as boolean;
    },
    installAllPaidPluginsVisible(): boolean {
      return ((this.getIsValidConsumer
        && this.isSuperUser
        && this.isAutoUpdatePossible
        && this.isPluginsAdminEnabled
      ) || (
        this.installDisabled && this.installLoading
      )) as boolean;
    },
    showThemes(): boolean {
      return MatomoUrl.hashParsed.value.pluginType as string === 'themes';
    },
  },
  methods: {
    disableInstallAllPlugins(isLoading: boolean) {
      this.installDisabled = true;
      this.installLoading = isLoading;
    },
    enableInstallAllPlugins() {
      this.installDisabled = false;
      this.installLoading = false;
    },
    updateOverviewData() {
      this.updating = true;
      if (this.isSuperUser) {
        this.disableInstallAllPlugins(true);
      }

      if (this.fetchRequestAbortController) {
        this.fetchRequestAbortController.abort();
        this.fetchRequestAbortController = null;
      }

      this.fetchRequestAbortController = new AbortController();
      this.fetchRequest = AjaxHelper.post(
        {
          module: 'Marketplace',
          action: 'updateOverview',
          format: 'JSON',
        },
        {
        },
        {
          withTokenInUrl: true,
          abortController: this.fetchRequestAbortController,
        },
      ).then((response) => {
        this.updateData = response;
      }).finally(() => {
        this.updating = false;
        this.fetchRequestAbortController = null;
        this.enableInstallAllPlugins();
      });
    },
  },
});
</script>
