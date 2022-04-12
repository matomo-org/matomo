<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="contentTitle" id="geoip-db-mangement">
    <div v-if="showGeoipUpdateSection">
      <div v-if="!geoipDatabaseInstalled">
        <div v-show="showPiwikNotManagingInfo">
          <h3>{{ translate('GeoIp2_NotManagingGeoIPDBs') }}</h3>
          <div id="manage-geoip-dbs">
            <div
              class="row"
              id="geoipdb-screen1"
            >
              <div class="geoipdb-column-1 col s6">
                <p>{{ translate('GeoIp2_IWantToDownloadFreeGeoIP') }}<sup><small>*</small></sup></p>
              </div>
              <div class="geoipdb-column-2 col s6">
                <p v-html="$sanitize(purchasedGeoIpText)"></p>
              </div>
              <div class="geoipdb-column-1 col s6">
                <input
                  type="button"
                  class="btn"
                  @click="startDownloadFreeGeoIp()"
                  :value="`${translate('General_GetStarted')}...`"
                />
              </div>
              <div class="geoipdb-column-2 col s6">
                <input
                  type="button"
                  class="btn"
                  id="start-automatic-update-geoip"
                  @click="startAutomaticUpdateGeoIp()"
                  :value="`${translate('General_GetStarted')}...`"
                />
              </div>
            </div>
            <div class="row">
              <p><sup>* <small v-html="$sanitize(accuracyNote)"></small></sup></p>
            </div>
          </div>
        </div>
        <div
          id="geoipdb-screen2-download"
          v-show="showFreeDownload"
        >
          <div>
            <Progressbar
              :label="freeProgressbarLabel"
              :progress="progressFreeDownload"
            >
            </Progressbar>
          </div>
        </div>
      </div>

      <div
        id="geoipdb-update-info"
        v-if="geoipDatabaseInstalled && !downloadErrorMessage"
      >
        <p>
          <span v-html="$sanitize(geoIPUpdaterInstructions)"></span>
          <br /><br />
          <span v-if="!!dbipLiteUrl" v-html="$sanitize(geoliteCityLink)"></span>
          <span v-html="$sanitize(maxMindLinkExplanation)"></span>
          <span v-show="geoipDatabaseInstalled">
            <br /><br />{{ translate('GeoIp2_GeoIPUpdaterIntro') }}:
          </span>
        </p>
        <div>
          <Field
            uicontrol="text"
            name="geoip-location-db"
            :introduction="translate('GeoIp2_LocationDatabase')"
            :title="translate('Actions_ColumnDownloadURL')"
            :inline-help="translate('GeoIp2_LocationDatabaseHint')"
            v-model="locationDbUrl"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="text"
            name="geoip-isp-db"
            :introduction="translate('GeoIp2_ISPDatabase')"
            :title="translate('Actions_ColumnDownloadURL')"
            :inline-help="providerPluginHelp"
            v-model="ispDbUrl"
            :disabled="!isProviderPluginActive"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="radio"
            name="geoip-update-period"
            :introduction="translate('GeoIp2_DownloadNewDatabasesEvery')"
            v-model="updatePeriod"
            :options="updatePeriodOptions"
          >
            <template v-slot:inline-help>
              <div
                id="locationProviderUpdatePeriodInlineHelp"
                class="inline-help-node"
                ref="inlineHelpNode"
              >
                <span
                  v-if="lastTimeUpdaterRun"
                  v-html="$sanitize(
                    translate('GeoIp2_UpdaterWasLastRun', lastTimeUpdaterRun),
                  )"
                />
                <span v-else>{{ translate('GeoIp2_UpdaterHasNotBeenRun') }}</span>
                <br /><br />
                <div id="geoip-updater-next-run-time" v-html="$sanitize(nextRunTimeText)">
                </div>
              </div>
            </template>
          </Field>
        </div>
        <input
          type="button"
          class="btn"
          @click="saveGeoIpLinks()"
          :value="buttonUpdateSaveText"
        />
        <div>
          <div id="done-updating-updater" />
          <div id="geoipdb-update-info-error" />
          <div>
            <Progressbar
              v-show="isUpdatingGeoIpDatabase"
              :progress="progressUpdateDownload"
              :label="progressUpdateLabel"
            />
          </div>
        </div>
      </div>
      <div v-if="downloadErrorMessage" v-html="$sanitize(downloadErrorMessage)"></div>
    </div>
    <div v-else>
      <p class="form-description">{{ translate('GeoIp2_CannotSetupGeoIPAutoUpdating') }}</p>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Progressbar,
  ContentBlock,
  NotificationsStore,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface Geoip2UpdaterState {
  geoipDatabaseInstalled: boolean;
  showFreeDownload: boolean;
  showPiwikNotManagingInfo: boolean;
  progressFreeDownload: number;
  progressUpdateDownload: number;
  buttonUpdateSaveText: string;
  progressUpdateLabel: string;
  locationDbUrl: string;
  ispDbUrl: string;
  orgDbUrl: string;
  updatePeriod: string;
  isUpdatingGeoIpDatabase: boolean;
  downloadErrorMessage: string|null;
  nextRunTimePrettyUpdated?: string;
}

interface UpdateGeoIpLinksResponse {
  to_download?: string;
  to_download_label?: string;
  nextRunTime: string;
}

interface DownloadChunkResponse {
  current_size: number;
  expected_file_size: number;
  nextRunTime?: string;
  to_download?: string;
  to_download_label?: string;
  error?: string;
}

const { $ } = window;

export default defineComponent({
  props: {
    geoipDatabaseStartedInstalled: Boolean,
    showGeoipUpdateSection: {
      type: Boolean,
      required: true,
    },
    dbipLiteUrl: {
      type: String,
      required: true,
    },
    dbipLiteFilename: {
      type: String,
      required: true,
    },
    geoipLocUrl: String,
    isProviderPluginActive: Boolean,
    geoipIspUrl: String,
    lastTimeUpdaterRun: String,
    geoipUpdatePeriod: String,
    updatePeriodOptions: {
      type: Object,
      required: true,
    },
    nextRunTime: Number,
    nextRunTimePretty: String,
  },
  components: {
    Progressbar,
    Field,
    ContentBlock,
  },
  data(): Geoip2UpdaterState {
    return {
      geoipDatabaseInstalled: !!this.geoipDatabaseStartedInstalled,
      showFreeDownload: false,
      showPiwikNotManagingInfo: true,
      progressFreeDownload: 0,
      progressUpdateDownload: 0,
      buttonUpdateSaveText: translate('General_Save'),
      progressUpdateLabel: '',
      locationDbUrl: this.geoipLocUrl || '',
      ispDbUrl: this.geoipIspUrl || '',
      orgDbUrl: '',
      updatePeriod: this.geoipUpdatePeriod || 'month',
      isUpdatingGeoIpDatabase: false,
      downloadErrorMessage: null,
      nextRunTimePrettyUpdated: undefined,
    };
  },
  methods: {
    startDownloadFreeGeoIp() {
      this.showFreeDownload = true;
      this.showPiwikNotManagingInfo = false;
      this.progressFreeDownload = 0; // start download of free dbs

      this.downloadNextChunk(
        'downloadFreeDBIPLiteDB',
        (v) => {
          this.progressFreeDownload = v;
        },
        false,
        {},
      ).then(() => {
        window.location.reload();
      }).catch((e) => {
        this.geoipDatabaseInstalled = true;
        this.downloadErrorMessage = e.message;
      });
    },
    startAutomaticUpdateGeoIp() {
      this.buttonUpdateSaveText = translate('General_Continue');
      this.showGeoIpUpdateInfo();
    },
    showGeoIpUpdateInfo() {
      this.geoipDatabaseInstalled = true; // todo we need to replace this the proper way eventually
    },
    saveGeoIpLinks() {
      return AjaxHelper.post<UpdateGeoIpLinksResponse>(
        {
          period: this.updatePeriod,
          module: 'GeoIp2',
          action: 'updateGeoIPLinks',
        },
        {
          loc_db: this.locationDbUrl,
          isp_db: this.ispDbUrl,
          org_db: this.orgDbUrl,
        },
        {
          withTokenInUrl: true,
        },
      ).then(
        (response) => this.downloadNextFileIfNeeded(response, null),
      ).then((response) => {
        this.progressUpdateLabel = '';
        this.isUpdatingGeoIpDatabase = false;

        NotificationsStore.show({
          message: translate('General_Done'),
          placeat: '#done-updating-updater',
          context: 'success',
          noclear: true,
          type: 'toast',
          style: {
            display: 'inline-block',
          },
          id: 'userCountryGeoIpUpdate',
        });

        this.nextRunTimePrettyUpdated = response.nextRunTime;
        $(this.$refs.inlineHelpNode as HTMLElement).effect('highlight', {
          color: '#FFFFCB',
        }, 2000);

        return undefined;
      }).catch((e) => {
        this.isUpdatingGeoIpDatabase = false;

        NotificationsStore.show({
          message: e.message,
          placeat: '#geoipdb-update-info-error',
          context: 'error',
          style: {
            display: 'inline-block',
          },
          id: 'userCountryGeoIpUpdate',
          type: 'transient',
        });
      });
    },
    downloadNextFileIfNeeded(
      response: DownloadChunkResponse|UpdateGeoIpLinksResponse,
      currentDownloading?: string|null,
    ): Promise<DownloadChunkResponse|UpdateGeoIpLinksResponse> {
      if (response?.to_download) {
        const continuing = currentDownloading === response.to_download;

        this.progressUpdateDownload = 0;
        this.progressUpdateLabel = response.to_download_label!;
        this.isUpdatingGeoIpDatabase = true; // start/continue download

        return this.downloadNextChunk(
          'downloadMissingGeoIpDb',
          (v) => {
            this.progressUpdateDownload = v;
          },
          continuing,
          {
            key: response.to_download,
          },
        ).then((r) => this.downloadNextFileIfNeeded(r, response.to_download));
      }

      return Promise.resolve(response);
    },
    downloadNextChunk(
      action: string,
      progressBarSet: (value: number) => void,
      cont: boolean,
      extraData: QueryParameters,
    ): Promise<DownloadChunkResponse> {
      const data: QueryParameters = { ...extraData };

      return AjaxHelper.post<DownloadChunkResponse>(
        {
          module: 'GeoIp2',
          action,
          continue: cont ? 1 : 0,
        },
        data,
        { withTokenInUrl: true },
      ).catch(() => {
        throw new Error(translate('GeoIp2_FatalErrorDuringDownload'));
      }).then((response) => {
        if (response.error) {
          throw new Error(response.error!);
        }

        // update progress bar
        const newProgressVal = Math.floor(
          (response.current_size / response.expected_file_size) * 100,
        );

        // if incomplete, download next chunk, otherwise, show updater manager
        progressBarSet(Math.min(newProgressVal, 100));

        if (newProgressVal < 100) {
          return this.downloadNextChunk(action, progressBarSet, true, extraData);
        }

        return response;
      });
    },
  },
  computed: {
    nextRunTimeText() {
      if (this.nextRunTimePrettyUpdated) {
        return this.nextRunTimePrettyUpdated;
      }

      if (!this.nextRunTime) {
        return translate('GeoIp2_UpdaterIsNotScheduledToRun');
      }

      if (this.nextRunTime * 1000 < Date.now()) {
        return translate('GeoIp2_UpdaterScheduledForNextRun');
      }

      return translate(
        'GeoIp2_UpdaterWillRunNext',
        `<strong>${this.nextRunTimePretty}</strong>`,
      );
    },
    providerPluginHelp() {
      if (this.isProviderPluginActive) {
        return undefined;
      }

      const text = translate('GeoIp2_ISPRequiresProviderPlugin');
      return `<div style="margin:0" class='alert alert-warning'>${text}</div>`;
    },
    contentTitle() {
      return translate(
        this.geoipDatabaseInstalled ? 'GeoIp2_SetupAutomaticUpdatesOfGeoIP' : 'GeoIp2_GeoIPDatabases',
      );
    },
    accuracyNote() {
      return translate(
        'UserCountry_GeoIpDbIpAccuracyNote',
        '<a href="https://dev.maxmind.com/geoip/geoip2/geolite2/?rId=piwik" rel="noreferrer noopener" target="_blank">',
        '</a>',
      );
    },
    purchasedGeoIpText() {
      const maxMindLink = 'http://www.maxmind.com/en/geolocation_landing?rId=piwik';
      return translate(
        'GeoIp2_IPurchasedGeoIPDBs',
        `<a rel="noreferrer noopener" href="${maxMindLink}" target="_blank">`,
        '</a>',
        '<a rel="noreferrer noopener" href="https://db-ip.com/db/?refid=mtm" target="_blank">',
        '</a>',
      );
    },
    geoIPUpdaterInstructions() {
      return translate(
        'GeoIp2_GeoIPUpdaterInstructions',
        '<a href="http://www.maxmind.com/?rId=piwik" rel="noreferrer noopener" target="_blank">',
        '</a>',
        '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">',
        '</a>',
      );
    },
    geoliteCityLink() {
      const translation = translate(
        'GeoIp2_GeoLiteCityLink',
        `<a rel="noreferrer noopener" href="${this.dbipLiteUrl}" target="_blank">`,
        this.dbipLiteUrl,
        '</a>',
      );
      return `${translation}<br /><br />`;
    },
    maxMindLinkExplanation() {
      const link = 'https://matomo.org/faq/how-to/'
        + 'how-do-i-get-the-geolocation-download-url-for-the-free-maxmind-db/';
      return translate(
        'UserCountry_MaxMindLinkExplanation',
        `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
    freeProgressbarLabel() {
      return translate(
        'GeoIp2_DownloadingDb',
        `<a href="${this.dbipLiteUrl}">${this.dbipLiteFilename}</a>...`,
      );
    },
  },
});
</script>
