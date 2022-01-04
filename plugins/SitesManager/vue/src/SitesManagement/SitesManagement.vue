<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="SitesManager" ref="root">
    <div class="sites-manager-header">
      <div v-content-intro>
        <h2
          v-show="availableTypes.length"
        >
          <EnrichedHeadline
            :help-url="'https://matomo.org/docs/manage-websites/'"
            :feature-name="translate('SitesManager_WebsitesManagement')"
          >
            {{ headlineText }}
          </EnrichedHeadline>
        </h2>

        <p>
          {{ translate('SitesManager_MainDescription') }}

          <span v-html="$sanitize(mainDescription)"></span>

          <span v-show="hasSuperUserAccess">
            <br/>
            <span v-html="superUserAccessMessage"></span>
        </span>
        </p>
      </div>
    </div>
    <div>
      <div :class="{ hide_only: !isLoading }">
        <div class="loadingPiwik">
          <img
            src="plugins/Morpheus/images/loading-blue.gif"
            :alt="translate('General_LoadingData')"
          />
          {{ translate('General_LoadingData') }}
        </div>
      </div>
    </div>

    <div>
      <AddSiteLink
        :site-is-being-edited="isSiteBeingEdited"
        :has-prev="hasPrev"
        :hasNext="hasNext"
        :offset-start="offsetStart"
        :offset-end="offsetEnd"
        :total-number-of-sites="totalNumberOfSites"
        @add="addNewEntity()"
        @search="searchSites($event)"
        @prev="previousPage()"
        @next="nextPage()"
      />
    </div>

    <MatomoDialog v-model="showAddSiteDialog" class="ui-confirm">

      <h2>{{ translate('SitesManager_ChooseMeasurableTypeHeadline') }}</h2>

      <div class="center">
        <p>
          <button
            type="button"
            v-for="type in availableTypes"
            :key="type.id"
            :title="type.description"
            class="modal-close btn"
            style="margin-left: 20px;"
            @click="addSite(type.id);"
            aria-disabled="false"
          >
            <span class="ui-button-text">{{ type.name }}</span>
          </button>
        </p>
      </div>
    </MatomoDialog>

    <div class="sitesManagerList">
      <p v-if="searchTerm && 0 === sites.length && !isLoading">
        {{ translate('SitesManager_NotFound') }} <strong>{{ searchTerm }}</strong>
      </p>

      <div
        v-for="(site, index) in sites"
        :key="site.idsite"
      >
        <SiteFields
          :site="site"
          :timezone-support-enabled="timezoneSupportEnabled"
          :utc-time="utcTime"
          :global-settings="globalSettings"
          @cancel-edit-site="afterCancelEdit($event)"
          @delete="afterDelete($event)"
          @save="afterSave($event.site, $event.settingValues, index)"
        />
      </div>
    </div>

    <div class="bottomButtonBar">
      <AddSiteLink
        :site-is-being-edited="isSiteBeingEdited"
        :has-prev="hasPrev"
        :hasNext="hasNext"
        :offset-start="offsetStart"
        :offset-end="offsetEnd"
        :total-number-of-sites="totalNumberOfSites"
        @add="addNewEntity()"
        @search="searchSites($event)"
        @prev="previousPage()"
        @next="nextPage()"
      />
    </div>
  </div>

</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import {
  Matomo,
  MatomoDialog,
  Site,
  ContentIntro,
  EnrichedHeadline,
  AjaxHelper,
  MatomoUrl,
  translate,
} from 'CoreHome';
import { Setting } from 'CorePluginsAdmin';
import AddSiteLink from './AddSiteLink';
import SiteFields from '../SiteFields/SiteFields';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';
import TimezoneStore from '../TimezoneStore/TimezoneStore';
import GlobalSettingsStore from '../GlobalSettingsStore/GlobalSettingsStore';

interface SitesManagementState {
  pageSize: number;
  currentPage: number;
  showAddSiteDialog: boolean;
  searchTerm: string;
  sites: Site[];
  utcTime: Date;
  totalNumberOfSites: number|null;
  isLoadingInitialEntities: boolean;
  isSiteBeingEdited: boolean;
  fetchLimitedSitesAbortController: null|AbortController,
}

export default defineComponent({
  components: {
    MatomoDialog,
    AddSiteLink,
    SiteFields,
    EnrichedHeadline,
  },
  directives: {
    ContentIntro,
  },
  data(): SitesManagementState {
    const currentDate = new Date();
    const utcTime = new Date(
      currentDate.getUTCFullYear(),
      currentDate.getUTCMonth(),
      currentDate.getUTCDate(),
      currentDate.getUTCHours(),
      currentDate.getUTCMinutes(),
      currentDate.getUTCSeconds(),
    );

    return {
      pageSize: 10,
      currentPage: 0,
      showAddSiteDialog: false,
      searchTerm: '',
      sites: [],
      isLoadingInitialEntities: false,
      utcTime,
      totalNumberOfSites: null,
      isSiteBeingEdited: false,
      fetchLimitedSitesAbortController: null,
    };
  },
  created() {
    this.isLoadingInitialEntities = true;

    Promise.all([
      SiteTypesStore.fetchAvailableTypes(),
      this.fetchLimitedSitesWithAdminAccess(),
      this.getTotalNumberOfSites(),
    ]).then(() => {
      this.triggerAddSiteIfRequested();
    }).finally(() => {
      this.isLoadingInitialEntities = false;
    });

    // TODO: test
    // if hash is #globalSettings, redirect to globalSettings action
    watch(() => MatomoUrl.hashQuery.value, (newHash) => {
      if (Matomo.hasSuperUserAccess
        && (
          newHash === '#globalSettings'
          || newHash === '#/globalSettings'
        )
      ) {
        MatomoUrl.updateLocation({
          ...MatomoUrl.urlParsed,
          action: 'globalSettings',
        });
      }
    });
  },
  computed: {
    isLoading() {
      return !!this.fetchLimitedSitesAbortController
        || this.isLoadingInitialEntities
        || this.totalNumberOfSites === null
        || SiteTypesStore.isLoading.value
        || TimezoneStore.isLoading.value
        || GlobalSettingsStore.isLoading.value;
    },
    availableTypes() {
      return Object.values(SiteTypesStore.typesById.value);
    },
    timezoneSupportEnabled() {
      return TimezoneStore.timezoneSupportEnabled.value;
    },
    globalSettings() {
      return GlobalSettingsStore.globalSettings.value;
    },
    headlineText() {
      return translate(
        'SitesManager_XManagement',
        this.availableTypes.length > 1
          ? translate('General_Measurables')
          : translate('SitesManager_Sites'),
      );
    },
    mainDescription() {
      return translate(
        'SitesManager_YouCurrentlyHaveAccessToNWebsites',
        `<strong>${this.totalNumberOfSites}</strong>`
      );
    },
    hasSuperUserAccess() {
      return Matomo.hasSuperUserAccess;
    },
    superUserAccessMessage() {
      return translate('SitesManager_SuperUserAccessCan', '<a href=\'#globalSettings\'>', '</a>');
    },
    hasPrev() {
      return this.currentPage >= 1;
    },
    hasNext() {
      return this.sites.length === this.pageSize;
    },
    offsetStart() {
      return this.currentPage * this.pageSize + 1;
    },
    offsetEnd() {
      return this.offsetStart + this.sites.length - 1;
    },
  },
  methods: {
    addNewEntity() {
      if (this.availableTypes.length > 1) {
        this.showAddSiteDialog = true;
      } else if (this.availableTypes.length === 1) {
        this.addSite(this.availableTypes[0].id);
      }
    },
    addSite(typeId?: string) {
      let type: string|undefined = typeId;

      const parameters = {
        isAllowed: true,
        measurableType: type,
      };

      Matomo.postEvent('SitesManager.initAddSite', parameters); // TODO: test this

      if (parameters && !parameters.isAllowed) {
        return;
      }

      if (!type) {
        type = 'website'; // todo shall we really hard code this or trigger an exception or so?
      }

      this.sites.unshift({
        type,
      });
    },
    afterCancelEdit({ site, element }: { site: Site, element: HTMLElement }) {
      if (!site.idsite) {
        return;
      }

      this.isSiteBeingEdited = false;

      element.scrollIntoView();
    },
    fetchLimitedSitesWithAdminAccess() {
      if (this.fetchLimitedSitesAbortController) {
        this.fetchLimitedSitesAbortController.abort();
      }

      this.fetchLimitedSitesAbortController = new AbortController();

      const limit  = this.pageSize;
      const offset = this.currentPage * this.pageSize;

      const params: QueryParameters = {
        method: 'SitesManager.getSitesWithAdminAccess',
        fetchAliasUrls: true,
        limit: limit + offset, // this is applied in SitesManager.getSitesWithAdminAccess API
        filter_offset: offset, // filter_offset and filter_limit is applied in response builder
        filter_limit: limit,
      };

      if (this.searchTerm) {
        params.searchTerm = this.searchTerm;
      }

      return AjaxHelper<Site[]>.fetch(params).then((sites) => {
        this.sites = sites || [];
      }).finally(() => {
        this.fetchLimitedSitesAbortController = null;
      });
    },
    getTotalNumberOfSites() {
      return AjaxHelper.fetch<string[]>({
        method: 'SitesManager.getSitesIdWithAdminAccess',
        filter_limit: '-1',
      }).then((sites) => {
        this.totalNumberOfSites = sites.length;
      });
    },
    triggerAddSiteIfRequested() {
      const showaddsite = MatomoUrl.urlParsed.value.showaddsite as string;
      if (showaddsite === '1') {
        this.addNewEntity();
      }
    },
    previousPage() {
      this.currentPage = Math.max(0, this.currentPage - 1);
      this.fetchLimitedSitesWithAdminAccess();
    },
    nextPage() {
      this.currentPage = Math.max(0, this.currentPage + 1);
      this.fetchLimitedSitesWithAdminAccess();
    },
    searchSites(searchTerm: string) {
      this.searchTerm = searchTerm;
      this.currentPage = 0;
      this.fetchLimitedSitesWithAdminAccess();
    },
    afterDelete(site: Site) {
      let redirectParams: QueryParameters = {
        ...MatomoUrl.urlParsed.value,
      };

      delete redirectParams.showaddsite;

      // if the current idSite in the URL is the site we're deleting, then we have to make to
      // change it. otherwise, if a user goes to another page, the invalid idSite may cause
      // a fatal error.
      if (MatomoUrl.urlParsed.value.idSite === site.idsite) {
        const otherSite = this.sites.find((s) => s.idsite !== site.idsite);

        if (otherSite) {
          redirectParams = { ...redirectParams, idSite: otherSite.idsite };
        }
      }

      MatomoUrl.updateUrl(redirectParams, MatomoUrl.hashParsed.value);
    },
    afterSave(site: Site, settingValues: Record<string, Setting[]>, index: number) {
      const texttareaArrayParams = [
        'excluded_ips',
        'excluded_parameters',
        'excluded_user_agents',
        'sitesearch_keyword_parameters',
        'sitesearch_category_parameters',
      ];

      const newSite: Site = { ...site };

      Object.values(settingValues).forEach((settings) => {
        settings.forEach((setting) => {
          if (setting.name === 'urls') {
            newSite.alias_urls = setting.value as string[];
          } else if (texttareaArrayParams.indexOf(setting.name) !== -1) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (newSite as any)[setting.name] = (setting.value as string[]).join(', ');
          } else {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (newSite as any)[setting.name] = setting.value;
          }
        });
      });

      this.sites[index] = newSite;
    },
  },
});
</script>
