<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <Marketplace
      :default-sort="defaultSort"
      :install-all-paid-plugins-visible="installAllPaidPluginsVisible"
      :install-disabled="installDisabled"
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
      @triggerUpdate="updateOverviewData()"
      @startTrialStart="disableInstallAllPlugins(true)"
      @startTrialStop="disableInstallAllPlugins(false)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentIntro,
} from 'CoreHome';
import Marketplace from '../Marketplace/Marketplace.vue';

import { TObject } from '../types';

/**
 * The reporting page's site, period and segment selectors. Nothing in the Marketplace is scoped to
 * a site, a period or a segment, so in the reporting menu they would offer choices that change
 * nothing. The update notice beside them stays; the admin overview drops the top controls entirely.
 */
const REPORTING_SELECTORS = '.top_controls .top_bar_sites_selector, .top_controls #periodString, '
  + '.top_controls .segmentEditorPanel';

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
    defaultSort: {
      type: String,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
  },
  components: {
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
  mounted() {
    this.setReportingSelectorsHidden(true);
  },
  unmounted() {
    // the reporting page is a single page: the next category shown keeps the same top controls
    this.setReportingSelectorsHidden(false);
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
  },
  methods: {
    setReportingSelectorsHidden(hidden: boolean) {
      if (!this.inReportingMenu) {
        return;
      }

      document.querySelectorAll<HTMLElement>(REPORTING_SELECTORS).forEach((element) => {
        element.style.display = hidden ? 'none' : '';
      });
    },
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
