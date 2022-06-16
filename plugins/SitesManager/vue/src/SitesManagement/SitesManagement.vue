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
            <span v-html="$sanitize(superUserAccessMessage)"></span>
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
      <ButtonBar
        :site-is-being-edited="isSiteBeingEdited"
        :has-prev="hasPrev"
        :hasNext="hasNext"
        :offset-start="offsetStart"
        :offset-end="offsetEnd"
        :total-number-of-sites="totalNumberOfSites"
        :is-loading="isLoading"
        :search-term="searchTerm"
        :is-searching="!!activeSearchTerm"
        @update:search-term="searchTerm = $event"
        @add="addNewEntity()"
        @search="searchSites($event)"
        @prev="previousPage()"
        @next="nextPage()"
      />
    </div>

    <MatomoDialog v-model="showAddSiteDialog">
      <div class="ui-confirm">
        <div>
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
        </div>
      </div>
    </MatomoDialog>

    <div class="sitesManagerList">
      <p v-if="activeSearchTerm && 0 === sites.length && !isLoading">
        {{ translate('SitesManager_NotFound') }} <strong>{{ activeSearchTerm }}</strong>
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
          @edit-site="this.isSiteBeingEdited = true"
          @cancel-edit-site="afterCancelEdit($event)"
          @delete="afterDelete($event)"
          @save="afterSave($event.site, $event.settingValues, index, $event.isNew)"
        />
      </div>
    </div>

    <div class="bottomButtonBar">
      <ButtonBar
        :site-is-being-edited="isSiteBeingEdited"
        :has-prev="hasPrev"
        :hasNext="hasNext"
        :offset-start="offsetStart"
        :offset-end="offsetEnd"
        :total-number-of-sites="totalNumberOfSites"
        :is-loading="isLoading"
        :search-term="searchTerm"
        :is-searching="!!activeSearchTerm"
        @update:search-term="searchTerm = $event"
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
import ButtonBar from './ButtonBar.vue';
import SiteFields from '../SiteFields/SiteFields.vue';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';
import TimezoneStore from '../TimezoneStore/TimezoneStore';
import GlobalSettingsStore from '../GlobalSettingsStore/GlobalSettingsStore';

interface SitesManagementState {
  pageSize: number;
  currentPage: number;
  showAddSiteDialog: boolean;
  searchTerm: string;
  activeSearchTerm: string;
  fetchedSites: Site[];
  utcTime: Date;
  totalNumberOfSites: number|null;
  isLoadingInitialEntities: boolean;
  isSiteBeingEdited: boolean;
  fetchLimitedSitesAbortController: null|AbortController,
}

export default defineComponent({
  props: {
    // TypeScript can't add state types if there are no properties (probably a bug in Vue)
    // so we add one dummy property to get the compile to work
    dummy: String,
  },
  components: {
    MatomoDialog,
    ButtonBar,
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
      activeSearchTerm: '',
      fetchedSites: [],
      isLoadingInitialEntities: false,
      utcTime,
      totalNumberOfSites: null,
      isSiteBeingEdited: false,
      fetchLimitedSitesAbortController: null,
    };
  },
  created() {
    TimezoneStore.init();
    SiteTypesStore.init();
    GlobalSettingsStore.init();

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

    // if hash is #globalSettings, redirect to globalSettings action (we don't do it on
    // page load so the back button still works)
    watch(() => MatomoUrl.hashQuery.value, () => {
      this.checkGlobalSettingsHash();
    });
  },
  computed: {
    sites() {
      const emptyIdSiteRows = this.fetchedSites.filter((s) => !s.idsite).length;
      return this.fetchedSites.slice(0, this.pageSize + emptyIdSiteRows);
    },
    isLoading() {
      return !!this.fetchLimitedSitesAbortController
        || this.isLoadingInitialEntities
        || this.totalNumberOfSites === null
        || SiteTypesStore.isLoading.value
        || TimezoneStore.isLoading.value
        || GlobalSettingsStore.isLoading.value;
    },
    availableTypes() {
      return SiteTypesStore.types.value;
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
        `<strong>${this.totalNumberOfSites}</strong>`,
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
      return this.fetchedSites.filter((s) => !!s.idsite).length >= this.pageSize + 1;
    },
    offsetStart() {
      return this.currentPage * this.pageSize + 1;
    },
    offsetEnd() {
      return this.offsetStart + this.sites.filter((s) => !!s.idsite).length - 1;
    },
  },
  methods: {
    checkGlobalSettingsHash() {
      const newHash = MatomoUrl.hashQuery.value;
      if (Matomo.hasSuperUserAccess
        && (
          newHash === 'globalSettings'
          || newHash === '/globalSettings'
        )
      ) {
        MatomoUrl.updateLocation({
          ...MatomoUrl.urlParsed.value,
          action: 'globalSettings',
        });
      }
    },
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

      Matomo.postEvent('SitesManager.initAddSite', parameters);

      if (parameters && !parameters.isAllowed) {
        return;
      }

      if (!type) {
        type = 'website'; // todo shall we really hard code this or trigger an exception or so?
      }

      this.fetchedSites.unshift({
        type,
      } as unknown as Site);

      this.isSiteBeingEdited = true;
    },
    afterCancelEdit({ site, element }: { site: Site, element: HTMLElement }) {
      this.isSiteBeingEdited = false;

      if (!site.idsite) {
        this.fetchedSites = this.fetchedSites.filter((s) => !!s.idsite);
        return;
      }

      element.scrollIntoView();
    },
    fetchLimitedSitesWithAdminAccess(searchTerm = '') {
      if (this.fetchLimitedSitesAbortController) {
        this.fetchLimitedSitesAbortController.abort();
      }

      this.fetchLimitedSitesAbortController = new AbortController();

      const limit = this.pageSize + 1;
      const offset = this.currentPage * this.pageSize;

      const params: QueryParameters = {
        method: 'SitesManager.getSitesWithAdminAccess',
        fetchAliasUrls: 1,
        limit: limit + offset, // this is applied in SitesManager.getSitesWithAdminAccess API
        filter_offset: offset, // filter_offset and filter_limit is applied in response builder
        filter_limit: limit,
      };

      if (searchTerm) {
        params.pattern = searchTerm;
      }

      return AjaxHelper.fetch<Site[]>(params).then((sites) => {
        this.fetchedSites = sites || [];
      }).then((sites) => {
        this.activeSearchTerm = searchTerm;
        return sites;
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
      const forcedEditSiteId = SiteTypesStore.getEditSiteIdParameter();
      const showaddsite = MatomoUrl.urlParsed.value.showaddsite as string;

      if (showaddsite === '1') {
        this.addNewEntity();
      } else if (forcedEditSiteId) {
        this.searchTerm = forcedEditSiteId;
        this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
      }
    },
    previousPage() {
      this.currentPage = Math.max(0, this.currentPage - 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    nextPage() {
      this.currentPage = Math.max(0, this.currentPage + 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    searchSites() {
      this.currentPage = 0;
      this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
    },
    afterDelete(site: Site) {
      let redirectParams: QueryParameters = {
        showaddsite: 0,
      };

      // if the current idSite in the URL is the site we're deleting, then we have to make to
      // change it. otherwise, if a user goes to another page, the invalid idSite may cause
      // a fatal error.
      if (MatomoUrl.urlParsed.value.idSite === `${site.idsite}`) {
        const otherSite = this.sites.find((s) => s.idsite !== site.idsite);

        if (otherSite) {
          redirectParams = { ...redirectParams, idSite: otherSite.idsite };
        }
      }

      Matomo.helper.redirect(redirectParams);
    },
    afterSave(site: Site, settingValues: Record<string, Setting[]>, index: number, isNew: boolean) {
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

      this.fetchedSites[index] = newSite;

      if (isNew && this.totalNumberOfSites !== null) {
        this.totalNumberOfSites += 1;
      }

      this.isSiteBeingEdited = false;
    },
  },
});
</script>
