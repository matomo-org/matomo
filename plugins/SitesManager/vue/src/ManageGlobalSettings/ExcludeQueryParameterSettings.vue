<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="siteManagerGlobalExcludedUrlParameters">
      <div id="excludedQueryParametersGlobalHelp" class="inline-help-node">
        <div>
          {{ translate('SitesManager_ListOfQueryParametersToExclude', '/^sess.*|.*[dD]ate$/') }}
        </div>
      </div>

      <div id="excludedQueryParametersGlobalExclusionTypeHelp" class="inline-help-node">
        <div v-show="localExclusionTypeForQueryParams === 'common_session_parameters'">
          {{ translate('SitesManager_ExclusionTypeDescriptionCommonSessionParameters') }}
        </div>
        <div v-show="localExclusionTypeForQueryParams === 'matomo_recommended_pii'">
          <p>{{ translate('SitesManager_ExclusionTypeDescriptionMatomoRecommendedPII') }}</p>
          <div>
            <a href="javascript:;"
               v-if="!showListOfCommonExclusions"
               @click.prevent="showListOfCommonExclusions = true">
                {{ translate('SitesManager_ExclusionViewListLink') }}
              <span class="icon-chevron-down"></span>
            </a>
            <a href="javascript:;"
               v-if="showListOfCommonExclusions"
               @click.prevent="showListOfCommonExclusions = false">
                {{ translate('SitesManager_ExclusionViewListLink') }}
              <span class="icon-chevron-up"></span>
            </a>
          </div>
          <div v-if="showListOfCommonExclusions">
            {{ commonSensitiveQueryParams.join(', ') }}
          </div>
          <br/><br/>
          {{ translate(
            'SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition',
            'phpsessid, sessionid, ...',
          ) }}
        </div>
        <div v-show="localExclusionTypeForQueryParams === 'custom'">
          {{ translate('SitesManager_ExclusionTypeDescriptionCustom') }}
          <br/><br/>
          {{ translate(
            'SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition',
            'phpsessid, sessionid, ...',
          ) }}
        </div>
      </div>

      <div>
        <Field
          uicontrol="radio"
          name="exclusionType"
          :introduction="translate('SitesManager_GlobalListExcludedQueryParameters')"
          :options="exclusionTypeOptions"
          v-model="localExclusionTypeForQueryParams"
          :inline-help="'#excludedQueryParametersGlobalExclusionTypeHelp'"
        />
      </div>

      <div v-show="localExclusionTypeForQueryParams === 'custom'">
        <Field
          uicontrol="textarea"
          name="excludedQueryParametersGlobal"
          var-type="array"
          class="limited-height-scrolling-textarea"
          v-model="localExcludedQueryParametersGlobal"
          :model-value="localExcludedQueryParametersGlobal.join('\n')"
          @input="onInputExcludedQueryParametersGlobal($event.target.value)"
          :title="translate('SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites')"
          :inline-help="'#excludedQueryParametersGlobalHelp'"
        />
        <input
          type="button"
          @click="addCommonPIIQueryParams()"
          class="btn"
          :value="translate('SitesManager_AddSensibleExclusionsToMyCustomListButtonText')"
        />
      </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface ExclusionTypeOption {
  value: string;
  key: 'common_session_parameters' | 'matomo_recommended_pii' | 'custom';
}

interface ExcludeQueryParameterSettingsState {
  localExclusionTypeForQueryParams: string;
  localExcludedQueryParametersGlobal: string[];
  exclusionTypeOptions: ExclusionTypeOption[];
  showListOfCommonExclusions: boolean;
}

export default defineComponent({
  components: {
    Field,
  },
  props: {
    exclusionTypeForQueryParams: {
      type: String,
      default: 'common_session_parameters',
    },
    excludedQueryParametersGlobal: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    commonSensitiveQueryParams: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  data(): ExcludeQueryParameterSettingsState {
    return {
      localExclusionTypeForQueryParams: this.exclusionTypeForQueryParams,
      localExcludedQueryParametersGlobal: this.excludedQueryParametersGlobal,
      exclusionTypeOptions: [
        {
          value: translate('SitesManager_ExclusionTypeOptionCommonSessionParameters'),
          key: 'common_session_parameters',
        },
        {
          value: translate('SitesManager_ExclusionTypeOptionMatomoRecommendedPII'),
          key: 'matomo_recommended_pii',
        },
        {
          value: translate('SitesManager_ExclusionTypeOptionCustom'),
          key: 'custom',
        },
      ],
      showListOfCommonExclusions: false,
    };
  },
  watch: {
    exclusionTypeForQueryParams: {
      handler(newExclusionType: string) {
        this.localExclusionTypeForQueryParams = newExclusionType;
      },
    },
    localExclusionTypeForQueryParams: {
      handler(newExclusionType: string) {
        this.updateExclusionType(newExclusionType);
      },
      immediate: true,
    },
    excludedQueryParametersGlobal: {
      handler(excludedQueryParametersGlobal: string[]) {
        this.localExcludedQueryParametersGlobal = excludedQueryParametersGlobal;
      },
    },
  },
  methods: {
    updateExclusionType(value: string) {
      if (value !== 'custom') {
        this.localExcludedQueryParametersGlobal = [];
        this.onInputExcludedQueryParametersGlobal('');
      }

      this.$emit('update:exclusionTypeForQueryParams', value);
    },
    onInputExcludedQueryParametersGlobal(value: string) {
      const valueArray = value.split('\n');
      this.$emit('update:excludedQueryParametersGlobal', valueArray);
    },
    addCommonPIIQueryParams() {
      let updatedParams = this.localExcludedQueryParametersGlobal.filter(
        (param) => !this.commonSensitiveQueryParams.includes(param),
      );
      updatedParams = updatedParams.concat(this.commonSensitiveQueryParams);
      this.localExcludedQueryParametersGlobal = updatedParams;
      this.$emit('update:excludedQueryParametersGlobal', updatedParams);
    },
  },
});
</script>
