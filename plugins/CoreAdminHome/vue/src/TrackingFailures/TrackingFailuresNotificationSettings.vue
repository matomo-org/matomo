<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="trackingFailuresNotificationSettings"
    :content-title="translate('CoreAdminHome_TrackingFailuresEmailNotifications')"
  >
    <p>{{ translate('CoreAdminHome_TrackingFailuresEmailNotificationsIntro') }}</p>
    <div class="trackingFailuresNotificationSettings__row">
      <div class="trackingFailuresNotificationSettings__label">
        <strong>{{ translate('CoreAdminHome_TrackingFailuresSendEmails') }}</strong>
        <div class="trackingFailuresNotificationSettings__help">
          {{ translate('CoreAdminHome_TrackingFailuresSendEmailsHelp') }}
        </div>
      </div>
      <div class="trackingFailuresNotificationSettings__control">
        <div class="switch">
          <label>
            <input
              v-model="enabled"
              class="trackingFailuresNotificationSettings__enabled"
              :aria-label="translate('CoreAdminHome_TrackingFailuresSendEmails')"
              :disabled="isSaving"
              type="checkbox"
            />
            <span class="lever" />
            {{ enabledText }}
          </label>
        </div>
      </div>
    </div>
    <div class="trackingFailuresNotificationSettings__row">
      <div class="trackingFailuresNotificationSettings__label">
        <strong>{{ translate('CoreAdminHome_TrackingFailuresExcludedWebsites') }}</strong>
        <div class="trackingFailuresNotificationSettings__help">
          {{ translate('CoreAdminHome_TrackingFailuresExcludedWebsitesHelp') }}
        </div>
      </div>
      <div class="trackingFailuresNotificationSettings__control">
        <p
          v-if="!sites.length"
          class="trackingFailuresNotificationSettings__noSites"
        >{{ translate('CoreAdminHome_TrackingFailuresNoExcludedWebsites') }}</p>
        <ul
          v-else
          class="trackingFailuresNotificationSettings__siteList"
        >
          <li
            v-for="site in sites"
            :key="site.id"
            class="trackingFailuresNotificationSettings__siteItem"
          >
            {{ site.name }}
            <button
              type="button"
              class="trackingFailuresNotificationSettings__siteRemove icon-close"
              :title="removeText(site)"
              :aria-label="removeText(site)"
              :disabled="isSaving"
              @click="removeSite(site)"
            />
          </li>
        </ul>
        <div class="trackingFailuresNotificationSettings__sitePicker">
          <div class="trackingFailuresNotificationSettings__siteSelector">
            <SiteSelector
              v-model="selectedSite"
              :show-selected-site="true"
              :show-all-sites-item="false"
              :switch-site-on-select="false"
              :default-to-first-site="true"
            />
          </div>
          <button
            type="button"
            class="btn btn-flat trackingFailuresNotificationSettings__siteAdd"
            :disabled="!canAddSelectedSite"
            @click="addSelectedSite()"
          >{{ translate('General_Add') }}</button>
        </div>
      </div>
    </div>
    <SaveButton
      :saving="isSaving"
      @confirm="save()"
    />
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  AjaxHelper,
  ContentBlock,
  NotificationsStore,
  SiteSelector,
  translate,
} from 'CoreHome';
import type { SiteRef } from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';

export interface TrackingFailuresNotificationSettingsState {
  enabled: boolean;
  sites: SiteRef[];
  selectedSite: SiteRef|null;
  isSaving: boolean;
}

export default defineComponent({
  props: {
    notificationsEnabled: {
      type: Boolean,
      required: true,
    },
    excludedSites: {
      type: Array as PropType<SiteRef[]>,
      required: true,
    },
  },
  emits: ['saved'],
  components: {
    ContentBlock,
    SaveButton,
    SiteSelector,
  },
  data(): TrackingFailuresNotificationSettingsState {
    return {
      enabled: this.notificationsEnabled,
      sites: [...this.excludedSites],
      selectedSite: null,
      isSaving: false,
    };
  },
  computed: {
    enabledText(): string {
      return translate(this.enabled
        ? 'CoreAdminHome_TrackingFailuresSendEmailsEnabled'
        : 'CoreAdminHome_TrackingFailuresSendEmailsDisabled');
    },
    canAddSelectedSite(): boolean {
      const selectedId = this.selectedSite?.id;

      return !this.isSaving
        && !!selectedId
        && !this.sites.some((site) => `${site.id}` === `${selectedId}`);
    },
  },
  methods: {
    addSelectedSite() {
      if (this.selectedSite && this.canAddSelectedSite) {
        this.sites.push({ ...this.selectedSite });
      }
    },
    removeText(site: SiteRef): string {
      return translate('CoreAdminHome_TrackingFailuresRemoveExcludedWebsite', site.name);
    },
    removeSite(site: SiteRef) {
      this.sites = this.sites.filter((s) => s !== site);
    },
    save() {
      this.isSaving = true;

      AjaxHelper.post<SiteRef[]>(
        { module: 'API', method: 'CoreAdminHome.setTrackingFailuresNotificationSettings' },
        {
          enabled: this.enabled ? 1 : 0,
          excludedIdSites: this.sites.map((site) => site.id).join(','),
        },
      ).then((savedSites) => {
        // the server leaves out websites that were deleted in the meantime
        this.sites = savedSites.map((site) => ({ id: site.id, name: site.name }));
        this.$emit('saved', this.sites.map((site) => Number(site.id)));

        const notificationId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'trackingFailuresNotificationSettings',
          context: 'success',
        });
        NotificationsStore.scrollToNotification(notificationId);
      }).catch(() => {
        // AjaxHelper already shows the error
      }).finally(() => {
        this.isSaving = false;
      });
    },
  },
});
</script>
