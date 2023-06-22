<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('CoreAdminHome_ImageTracking')"
    anchor="imageTracking"
  >
    <a name="image-tracking-link"></a>

    <div id="image-tracking-code-options">

      <p v-html="$sanitize(imageTrackingIntro)"></p>
      <p v-html="$sanitize(imageTrackingIntro3)"></p>

      <!-- website -->
      <Field
        uicontrol="site"
        name="image-tracker-website"
        v-model="site"
        :introduction="translate('General_Website')"
      />

      <!-- action_name -->
      <Field
        uicontrol="text"
        name="image-tracker-action-name"
        :model-value="pageName"
        @update:model-value="pageName = $event; updateTrackingCode()"
        :disabled="isLoading"
        :introduction="translate('General_Options')"
        :title="translate('Actions_ColumnPageName')"
      />

      <!-- goal -->
      <Field
        uicontrol="checkbox"
        name="image-tracking-goal-check"
        :model-value="trackGoal"
        @update:model-value="trackGoal = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_TrackAGoal')"
      />

      <div v-show="trackGoal"
           id="image-tracking-goal-sub">
        <div class="row">
          <div class="col s12 m6">
            <Field
              uicontrol="select"
              name="image-tracker-goal"
              :options="siteGoals"
              :disabled="isLoading"
              :model-value="trackIdGoal"
              @update:model-value="trackIdGoal = $event; updateTrackingCode()"
            />
          </div>
          <div class="col s12 m6">
            <Field
              uicontrol="text"
              name="image-revenue"
              :model-value="revenue"
              @update:model-value="revenue = $event; updateTrackingCode()"
              :disabled="isLoading"
              :full-width="true"
              :title="`${translate('CoreAdminHome_WithOptionalRevenue')} ${currentSiteCurrency}`"
            />
          </div>
        </div>
      </div>

      <div id="image-link-output-section">
        <h3>{{ translate('CoreAdminHome_ImageTrackingLink') }}</h3>

        <div id="image-tracking-text">
          <div>
            <pre v-copy-to-clipboard="{}" v-text="trackingCode" ref="trackingCode"></pre>
          </div>
        </div>
      </div>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  AjaxHelper,
  SiteRef,
  Site,
  CopyToClipboard,
  debounce,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface Goal {
  idgoal: string|number;
  name: string;
}

interface ImageTrackingCodeGeneratorState {
  isLoading: boolean;
  site: SiteRef;
  pageName: string;
  trackGoal: boolean;
  trackIdGoal: string|null;
  revenue: string;
  trackingCode: string;
  sites: Record<string, Site>;
  goals: Record<string, Goal[]>;
  trackingCodeAbortController: AbortController|null;
  isHighlighting: boolean;
}

interface GetImageTrackingResponse {
  value: string;
}

type CurrencyApiResponse = Record<string, string>;

let currencySymbols: CurrencyApiResponse|null = null;

const { $ } = window;

const piwikHost = window.location.host;
const piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));

export default defineComponent({
  props: {
    defaultSite: {
      type: Object,
      required: true,
    },
  },
  components: {
    ContentBlock,
    Field,
  },
  directives: {
    CopyToClipboard,
  },
  data(): ImageTrackingCodeGeneratorState {
    return {
      isLoading: false,
      site: this.defaultSite as SiteRef,
      pageName: '',
      trackGoal: false,
      trackIdGoal: null,
      revenue: '',
      trackingCode: '',
      sites: {},
      goals: {},
      trackingCodeAbortController: null,
      isHighlighting: false,
    };
  },
  created() {
    this.updateTrackingCode = debounce(this.updateTrackingCode);

    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site(newValue: SiteRef) {
      this.onSiteChanged(newValue);
    },
  },
  methods: {
    onSiteChanged(newValue: SiteRef) {
      this.trackIdGoal = null;

      let currencyPromise: Promise<CurrencyApiResponse>;
      if (currencySymbols) {
        currencyPromise = Promise.resolve(currencySymbols!);
      } else {
        this.isLoading = true;
        currencyPromise = AjaxHelper.fetch<CurrencyApiResponse>({
          method: 'SitesManager.getCurrencySymbols',
          filter_limit: '-1',
        });
      }

      let sitePromise: Promise<Site>;
      if (this.sites[newValue.id]) {
        sitePromise = Promise.resolve(this.sites[newValue.id]);
      } else {
        this.isLoading = true;
        sitePromise = AjaxHelper.fetch<Site>({
          module: 'API',
          method: 'SitesManager.getSiteFromId',
          idSite: newValue.id,
        });
      }

      let goalPromise: Promise<Goal[]>;
      if (this.goals[newValue.id]) {
        goalPromise = Promise.resolve(this.goals[newValue.id]);
      } else {
        this.isLoading = true;
        goalPromise = AjaxHelper.fetch<Goal[]>({
          module: 'API',
          method: 'Goals.getGoals',
          filter_limit: '-1',
          idSite: newValue.id,
        });
      }

      return Promise.all([
        currencyPromise,
        sitePromise,
        goalPromise,
      ]).then(([currencyResponse, site, goalsResponse]) => {
        this.isLoading = false;

        currencySymbols = currencyResponse as CurrencyApiResponse;
        this.sites[newValue.id] = site as Site;
        this.goals[newValue.id] = goalsResponse as Goal[];

        this.updateTrackingCode();
      });
    },
    updateTrackingCode() {
      // get data used to generate the link
      const postParams: Record<string, unknown> = {
        piwikUrl: `${piwikHost}${piwikPath}`,
        actionName: this.pageName,
        forceMatomoEndpoint: 1,
      };

      if (this.trackGoal && this.trackIdGoal) {
        postParams.idGoal = this.trackIdGoal;
        postParams.revenue = this.revenue;
      }

      if (this.trackingCodeAbortController) {
        this.trackingCodeAbortController.abort();
        this.trackingCodeAbortController = null;
      }

      this.trackingCodeAbortController = new AbortController();
      AjaxHelper.post<GetImageTrackingResponse>(
        {
          module: 'API',
          format: 'json',
          method: 'SitesManager.getImageTrackingCode',
          idSite: this.site.id,
        },
        postParams,
        { abortController: this.trackingCodeAbortController },
      ).then((response) => {
        this.trackingCodeAbortController = null;

        this.trackingCode = response.value;

        const imageCodeTextarea = $(this.$refs.trackingCode as HTMLElement);
        if (imageCodeTextarea && !this.isHighlighting) {
          this.isHighlighting = true;
          imageCodeTextarea.effect('highlight', {
            complete: () => {
              this.isHighlighting = false;
            },
          }, 1500);
        }
      });
    },
  },
  computed: {
    currentSiteCurrency() {
      if (!currencySymbols) {
        return '';
      }

      return currencySymbols[(this.sites[this.site.id].currency || '').toUpperCase()];
    },
    siteGoals() {
      const goalsResponse = this.goals[this.site.id];
      return [
        { key: '', value: translate('UserCountryMap_None') },
      ].concat(
        Object.values(goalsResponse || []).map((g) => ({ key: `${g.idgoal}`, value: g.name })),
      );
    },
    imageTrackingIntro() {
      const first = translate('CoreAdminHome_ImageTrackingIntro1');
      const second = translate(
        'CoreAdminHome_ImageTrackingIntro2',
        '<code>&lt;noscript&gt;&lt;/noscript&gt;</code>',
      );
      return `${first} ${second}`;
    },
    imageTrackingIntro3() {
      const link = 'https://matomo.org/docs/tracking-api/reference/';
      return translate(
        'CoreAdminHome_ImageTrackingIntro3',
        `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
  },
});

</script>
