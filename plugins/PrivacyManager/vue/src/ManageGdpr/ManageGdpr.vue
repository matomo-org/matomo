<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageGdpr">
    <ContentBlock :content-title="translate('PrivacyManager_GdprTools')">
      <div class="intro">
        <p>
          {{ translate('PrivacyManager_GdprToolsPageIntro1') }}
          <br /><br />
          {{ translate('PrivacyManager_GdprToolsPageIntro2') }}
          <br />
        </p>
        <ol>
          <li>{{ translate('PrivacyManager_GdprToolsPageIntroAccessRight') }}</li>
          <li>{{ translate('PrivacyManager_GdprToolsPageIntroEraseRight') }}</li>
        </ol>
        <p>
          <br />
          <span
            v-html="$sanitize(overviewHintText)"
          />
        </p>
      </div>
      <h3>{{ translate('PrivacyManager_SearchForDataSubject') }}</h3>
      <div class="form-group row">
        <div class="col s12 input-field">
          <div>
            <label
              for="gdprsite"
              class="siteSelectorLabel"
            >
              {{ translate('PrivacyManager_SelectWebsite') }}
            </label>
            <div class="sites_autocomplete">
              <SiteSelector
                id="gdprsite"
                v-model="site"
                :show-all-sites-item="true"
                :switch-site-on-select="false"
                :show-selected-site="true"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="form-group row segmentFilterGroup">
        <div class="col s12">
          <div>
            <label style="margin: 8px 0;display: inline-block;">
              {{ translate('PrivacyManager_FindDataSubjectsBy') }}
            </label>
            <div>
              <SegmentGenerator
                v-model="segment_filter"
                :visit-segments-only="true"
                :idsite="site.id"
              />
            </div>
          </div>
        </div>
      </div>
      <SaveButton
        class="findDataSubjects"
        :value="translate('PrivacyManager_FindMatchingDataSubjects')"
        @confirm="findDataSubjects()"
        :disabled="!segment_filter"
        :saving="isLoading"
      >
      </SaveButton>
    </ContentBlock>
    <div v-show="!dataSubjects.length && hasSearched">
      <h2>{{ translate('PrivacyManager_NoDataSubjectsFound') }}</h2>
    </div>
    <div v-show="dataSubjects.length">
      <h2>{{ translate('PrivacyManager_MatchingDataSubjects') }}</h2>
      <p>{{ translate('PrivacyManager_VisitsMatchedCriteria') }}
        {{ translate('PrivacyManager_ExportingNote') }}
        <br /> <br />
        {{ translate('PrivacyManager_DeletionFromMatomoOnly') }}
        <br /><br />
        {{ translate('PrivacyManager_ResultIncludesAllVisits') }}
      </p>
      <table v-content-table>
        <thead>
          <tr>
            <th class="checkInclude">
              <div>
                <Field
                  uicontrol="checkbox"
                  name="activateAll"
                  :model-value="toggleAll"
                  @update:model-value="toggleAll = $event; toggleActivateAll()"
                  :full-width="true"
                >
                </Field>
              </div>
            </th>
            <th>{{ translate('General_Website') }}</th>
            <th>{{ translate('General_VisitId') }}</th>
            <th>{{ translate('General_VisitorID') }}</th>
            <th>{{ translate('General_VisitorIP') }}</th>
            <th>{{ translate('General_UserId') }}</th>
            <th>{{ translate('General_Details') }}</th>
            <th v-show="profileEnabled">{{ translate('General_Action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-show="dataSubjects.length > 400">
            <td colspan="8">{{ translate('PrivacyManager_ResultTruncated', '400') }}</td>
          </tr>
          <tr
            :title="`${translate('PrivacyManager_LastAction')}: ${dataSubject.lastActionDateTime}`"
            v-for="(dataSubject, index) in dataSubjects"
            :key="index"
          >
            <td class="checkInclude">
              <div>
                <Field
                  uicontrol="checkbox"
                  :name="`subject${dataSubject.idVisit}`"
                  v-model="dataSubjectsActive[index]"
                  :full-width="true"
                >
                </Field>
              </div>
            </td>
            <td
              class="site"
              :title="`(${translate('General_Id')} ${dataSubject.idSite})`"
            >{{ dataSubject.siteName }}</td>
            <td class="visitId">{{ dataSubject.idVisit }}</td>
            <td class="visitorId">
              <a
                :title="translate('PrivacyManager_AddVisitorIdToSearch')"
                @click="addFilter('visitorId', dataSubject.visitorId)"
              >{{ dataSubject.visitorId }}</a>
            </td>
            <td class="visitorIp">
              <a
                :title="translate('PrivacyManager_AddVisitorIPToSearch')"
                @click="addFilter('visitIp', dataSubject.visitIp)"
              >{{ dataSubject.visitIp }}</a>
            </td>
            <td class="userId">
              <a
                :title="translate('PrivacyManager_AddUserIdToSearch')"
                @click="addFilter('userId', dataSubject.userId)"
              >{{ dataSubject.userId }}</a>
            </td>
            <td>
              <span
                :title="`${dataSubject.deviceType} ${dataSubject.deviceModel}`"
                style="margin-right:3.5px"
              >
                <img
                  height="16"
                  :src="dataSubject.deviceTypeIcon"
                />
              </span>
              <span
                :title="dataSubject.operatingSystem"
                style="margin-right:3.5px"
              >
                <img
                  height="16"
                  :src="dataSubject.operatingSystemIcon"
                />
              </span>
              <span
                :title="`${dataSubject.browser} ${dataSubject.browserFamilyDescription}`"
                style="margin-right:3.5px"
              >
                <img
                  height="16"
                  :src="dataSubject.browserIcon"
                />
              </span>
              <span
                :title="`${dataSubject.country} ${dataSubject.region || ''}`"
              >
                <img
                  height="16"
                  :src="dataSubject.countryFlag"
                />
              </span>
            </td>
            <td v-show="profileEnabled">
              <a
                class="visitorLogTooltip"
                title="View visitor profile"
                @click="showProfile(dataSubject.visitorId, dataSubject.idSite)"
              >
                <img src="plugins/Live/images/visitorProfileLaunch.png" style="margin-right:3.5px"/>
                <span>{{ translate('Live_ViewVisitorProfile') }}</span>
              </a>
            </td>
          </tr>
        </tbody>
      </table>
      <SaveButton
        class="exportDataSubjects"
        style="margin-right:3.5px"
        @confirm="exportDataSubject()"
        :disabled="!hasActiveDataSubjects"
        :value="translate('PrivacyManager_ExportSelectedVisits')"
      >
      </SaveButton>
      <SaveButton
        class="deleteDataSubjects"
        @confirm="deleteDataSubject()"
        :disabled="!hasActiveDataSubjects || isDeleting"
        :value="translate('PrivacyManager_DeleteSelectedVisits')"
      >
      </SaveButton>
    </div>
    <div
      class="ui-confirm"
      id="confirmDeleteDataSubject"
      ref="confirmDeleteDataSubject"
    >
      <h2>{{ translate('PrivacyManager_DeleteVisitsConfirm') }}</h2>
      <input
        role="yes"
        type="button"
        :value="translate('General_Yes')"
      />
      <input
        role="no"
        type="button"
        :value="translate('General_No')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  AjaxHelper,
  ContentBlock,
  SiteSelector,
  ContentTable,
  NotificationsStore,
  MatomoUrl,
} from 'CoreHome';
import { SegmentGenerator } from 'SegmentEditor';
import { SaveButton, Field } from 'CorePluginsAdmin';

interface DataSubject {
  browser: string;
  browserFamilyDescription: string;
  browserIcon: string;
  country: string;
  countryFlag: string;
  deviceModel: string;
  deviceType: string;
  deviceTypeIcon: string;
  idSite: string|number;
  idVisit: string;
  lastActionDateTime: string;
  operatingSystem: string;
  operatingSystemIcon: string;
  region: string|null;
  siteName: string;
  userId: string|null;
  visitIp: string;
  visitorId: string;
}

interface ManageGdprState {
  isLoading: boolean;
  isDeleting: boolean;
  site: Record<string, string>;
  segment_filter: string;
  dataSubjects: DataSubject[];
  toggleAll: boolean;
  hasSearched: boolean;
  profileEnabled: boolean;
  dataSubjectsActive: boolean[];
}

export default defineComponent({
  components: {
    ContentBlock,
    SiteSelector,
    SegmentGenerator,
    SaveButton,
    Field,
  },
  directives: {
    ContentTable,
  },
  data(): ManageGdprState {
    return {
      isLoading: false,
      isDeleting: false,
      site: {
        id: 'all',
        name: translate('UsersManager_AllWebsites'),
      },
      segment_filter: 'userId==',
      dataSubjects: [],
      toggleAll: true,
      hasSearched: false,
      profileEnabled: Matomo.visitorProfileEnabled,
      dataSubjectsActive: [],
    };
  },
  setup() {
    const sitesPromise = AjaxHelper.fetch<(string|number)[]>({
      method: 'SitesManager.getSitesIdWithAdminAccess',
      filter_limit: '-1',
    });

    return {
      getSites() {
        return sitesPromise;
      },
    };
  },
  methods: {
    showSuccessNotification(message: string) {
      const notificationInstanceId = NotificationsStore.show({
        message,
        context: 'success',
        id: 'manageGdpr',
        type: 'transient',
      });
      setTimeout(() => {
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }, 200);
    },
    linkTo(action: string) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'PrivacyManager',
        action,
      })}`;
    },
    toggleActivateAll() {
      this.dataSubjectsActive.fill(this.toggleAll);
    },
    showProfile(visitorId: string, idSite: string|number) {
      Matomo.helper.showVisitorProfilePopup(visitorId, idSite);
    },
    exportDataSubject() {
      const visitsToDelete = this.activatedDataSubjects;
      AjaxHelper.post<unknown[]>(
        {
          module: 'API',
          method: 'PrivacyManager.exportDataSubjects',
          format: 'json',
          filter_limit: -1,
        },
        {
          visits: visitsToDelete,
        },
      ).then((visits) => {
        this.showSuccessNotification(translate('PrivacyManager_VisitsSuccessfullyExported'));
        Matomo.helper.sendContentAsDownload('exported_data_subjects.json', JSON.stringify(visits));
      });
    },
    deleteDataSubject() {
      Matomo.helper.modalConfirm(this.$refs.confirmDeleteDataSubject as HTMLElement, {
        yes: () => {
          this.isDeleting = true;
          const visitsToDelete = this.activatedDataSubjects;
          AjaxHelper.post(
            {
              module: 'API',
              method: 'PrivacyManager.deleteDataSubjects',
              filter_limit: -1,
            },
            {
              visits: visitsToDelete,
            },
          ).then(() => {
            this.dataSubjects = [];
            this.showSuccessNotification(translate('PrivacyManager_VisitsSuccessfullyDeleted'));
            this.findDataSubjects();
          }).finally(() => {
            this.isDeleting = false;
          });
        },
      });
    },
    addFilter(segment: string, value: string) {
      this.segment_filter += `,${segment}==${value}`;
      this.findDataSubjects();
    },
    findDataSubjects() {
      this.dataSubjects = [];
      this.dataSubjectsActive = [];
      this.isLoading = true;
      this.toggleAll = true;
      this.hasSearched = false;

      this.getSites().then((idsites) => {
        let siteIds: QueryParameters[string] = this.site.id;

        if (siteIds === 'all' && !Matomo.hasSuperUserAccess) {
          // when superuser, we speed the request up a little and simply use 'all'
          siteIds = idsites;
          if (Array.isArray(idsites)) {
            siteIds = idsites.join(',');
          }
        }

        AjaxHelper.fetch<DataSubject[]>({
          idSite: siteIds,
          module: 'API',
          method: 'PrivacyManager.findDataSubjects',
          segment: this.segment_filter,
        }).then((visits) => {
          this.hasSearched = true;
          this.dataSubjectsActive = visits.map(() => true);
          this.dataSubjects = visits;
        }).finally(() => {
          this.isLoading = false;
        });
      });
    },
  },
  computed: {
    hasActiveDataSubjects(): boolean {
      return !!this.activatedDataSubjects.length;
    },
    activatedDataSubjects(): { idsite: string|number, idvisit: string|number }[] {
      return this.dataSubjects.filter((v, i) => this.dataSubjectsActive[i]).map((v) => ({
        idsite: v.idSite,
        idvisit: v.idVisit,
      }));
    },
    overviewHintText(): string {
      return translate(
        'PrivacyManager_GdprToolsOverviewHint',
        `<a href="${this.linkTo('gdprOverview')}">`,
        '</a>',
      );
    },
  },
});
</script>
