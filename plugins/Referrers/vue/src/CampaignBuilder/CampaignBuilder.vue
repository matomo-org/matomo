<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="campaignUrlBuilder">
    <form>
      <div>
        <Field
          uicontrol="text"
          name="websiteurl"
          :title="`${translate('Actions_ColumnPageURL')} (${translate('General_Required2')})`"
          v-model="websiteUrl"
          :inline-help="translate('Referrers_CampaignPageUrlHelp')"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignname"
          :title="`${translate('CoreAdminHome_JSTracking_CampaignNameParam')} (${
            translate('General_Required2')})`"
          v-model="campaignName"
          :inline-help="translate('Referrers_CampaignNameHelp')"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignkeyword"
          :title="translate('CoreAdminHome_JSTracking_CampaignKwdParam')"
          v-model="campaignKeyword"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignKeywordHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignsource"
          :title="translate('Referrers_CampaignSource')"
          v-show="hasExtraPlugin"
          v-model="campaignSource"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignSourceHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignmedium"
          :title="translate('Referrers_CampaignMedium')"
          v-show="hasExtraPlugin"
          v-model="campaignMedium"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignMediumHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaigncontent"
          :title="translate('Referrers_CampaignContent')"
          v-show="hasExtraPlugin"
          v-model="campaignContent"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignContentHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignid"
          :title="translate('Referrers_CampaignId')"
          v-show="hasExtraPlugin"
          v-model="campaignId"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignIdHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaigngroup"
          :title="translate('Referrers_CampaignGroup')"
          v-show="hasExtraPlugin"
          v-model="campaignGroup"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignGroupHelp')}`"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="campaignplacement"
          :title="translate('Referrers_CampaignPlacement')"
          v-show="hasExtraPlugin"
          v-model="campaignPlacement"
          :inline-help="`${translate('Goals_Optional')} ${
            translate('Referrers_CampaignPlacementHelp')}`"
        >
        </Field>
      </div>
      <SaveButton
        class="generateCampaignUrl"
        @confirm="generateUrl()"
        :disabled="!websiteUrl || !campaignName"
        :value="translate('Referrers_GenerateUrl')"
        style="margin-right:3.5px"
      >
      </SaveButton>
      <SaveButton
        class="resetCampaignUrl"
        @confirm="reset()"
        :value="translate('General_Clear')"
      >
      </SaveButton>
      <div v-show="generatedUrl">
        <h3>{{ translate('Referrers_URLCampaignBuilderResult') }}</h3>
        <div>
          <pre id="urlCampaignBuilderResult" v-copy-to-clipboard="{}"
            ><code v-text="generatedUrl" /></pre>
        </div>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { CopyToClipboard } from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';

interface CampaignBuilderState {
  websiteUrl: string;
  campaignName: string;
  campaignKeyword: string;
  campaignSource: string;
  campaignMedium: string;
  campaignId: string;
  campaignContent: string;
  campaignGroup: string;
  campaignPlacement: string;
  generatedUrl: string;
}

const { $ } = window;

export default defineComponent({
  props: {
    hasExtraPlugin: {
      type: Boolean,
      default: true,
    },
  },
  components: {
    Field,
    SaveButton,
  },
  directives: {
    CopyToClipboard,
  },
  data(): CampaignBuilderState {
    return {
      websiteUrl: '',
      campaignName: '',
      campaignKeyword: '',
      campaignSource: '',
      campaignMedium: '',
      campaignId: '',
      campaignContent: '',
      campaignGroup: '',
      campaignPlacement: '',
      generatedUrl: '',
    };
  },
  created() {
    this.reset();
  },
  watch: {
    generatedUrl() {
      $('#urlCampaignBuilderResult').effect('highlight', {}, 1500);
    },
  },
  methods: {
    reset() {
      this.websiteUrl = '';
      this.campaignName = '';
      this.campaignKeyword = '';
      this.campaignSource = '';
      this.campaignMedium = '';
      this.campaignId = '';
      this.campaignContent = '';
      this.campaignGroup = '';
      this.campaignPlacement = '';
      this.generatedUrl = '';
    },
    generateUrl() {
      let generatedUrl = String(this.websiteUrl);

      if (generatedUrl.indexOf('http') !== 0) {
        generatedUrl = `https://${generatedUrl.trim()}`;
      }

      const urlHashPos = generatedUrl.indexOf('#');

      let urlHash = '';
      if (urlHashPos >= 0) {
        urlHash = generatedUrl.slice(urlHashPos);
        generatedUrl = generatedUrl.slice(0, urlHashPos);
      }

      if (generatedUrl.indexOf('/', 10) < 0 && generatedUrl.indexOf('?') < 0) {
        generatedUrl += '/';
      }

      const campaignName = encodeURIComponent(this.campaignName.trim());

      if (generatedUrl.indexOf('?') > 0 || generatedUrl.indexOf('#') > 0) {
        generatedUrl += '&';
      } else {
        generatedUrl += '?';
      }

      generatedUrl += `mtm_campaign=${campaignName}`;

      if (this.campaignKeyword) {
        generatedUrl += `&mtm_kwd=${encodeURIComponent(this.campaignKeyword.trim())}`;
      }

      if (this.campaignSource) {
        generatedUrl += `&mtm_source=${encodeURIComponent(this.campaignSource.trim())}`;
      }

      if (this.campaignMedium) {
        generatedUrl += `&mtm_medium=${encodeURIComponent(this.campaignMedium.trim())}`;
      }

      if (this.campaignContent) {
        generatedUrl += `&mtm_content=${encodeURIComponent(this.campaignContent.trim())}`;
      }

      if (this.campaignId) {
        generatedUrl += `&mtm_cid=${encodeURIComponent(this.campaignId.trim())}`;
      }

      if (this.campaignGroup) {
        generatedUrl += `&mtm_group=${encodeURIComponent(this.campaignGroup.trim())}`;
      }

      if (this.campaignPlacement) {
        generatedUrl += `&mtm_placement=${encodeURIComponent(this.campaignPlacement.trim())}`;
      }

      generatedUrl += urlHash;

      this.generatedUrl = generatedUrl;
    },
  },
});
</script>
