<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-content-intro>
      <h2>
        <EnrichedHeadline>{{ translate('CustomDimensions_CustomDimensions') }}</EnrichedHeadline>
      </h2>
      <p v-html="$sanitize(contentIntroText)"></p>
      <p v-show="isLoading">
        <span class="loadingPiwik">
          <img src="plugins/Morpheus/images/loading-blue.gif" />
          {{ translate('General_LoadingData') }}
        </span>
      </p>
    </div>
    <div
      v-show="!isLoading"
      v-for="scope in availableScopes"
      :key="scope.value"
    >
      <ContentBlock
        :content-title="translate(`CustomDimensions_ScopeTitle${ucfirst(scope.value)}`)"
      >
        <p>
          {{ translate(`CustomDimensions_ScopeDescription${ucfirst(scope.value)}`) }}
          {{ translate(`CustomDimensions_ScopeDescription${ucfirst(scope.value)}MoreInfo`) }}
        </p>
        <table v-content-table>
          <thead>
            <tr>
              <th class="index">{{ translate('General_Id') }}</th>
              <th class="name">{{ translate('General_Name') }}</th>
              <th
                class="extractions"
                v-show="scope.supportsExtractions"
              >{{ translate('CustomDimensions_Extractions') }}</th>
              <th class="active">{{ translate('CorePluginsAdmin_Active') }}</th>
              <th class="action">{{ translate('General_Action') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-show="scope.numSlotsUsed === 0 && !isLoading">
              <td colspan="5">{{ translate('CustomDimensions_NoCustomDimensionConfigured') }}</td>
            </tr>
            <tr
              class="customdimension"
              v-for="customDimension in sortedCustomDimensionsByScope[scope.value]"
              :class="customDimension.idcustomdimension"
              :key="customDimension.idcustomdimension"
            >
              <td class="index">{{ customDimension.idcustomdimension }}</td>
              <td class="name">{{ customDimension.name }}</td>
              <td
                class="extractions"
                v-show="scope.supportsExtractions"
              >
                <span :class="{'icon-ok': customDimension.extractions[0]?.pattern}" />
              </td>
              <td class="active"><span :class="{'icon-ok': customDimension.active}" /></td>
              <td class="action">
                <a
                  class="table-action icon-edit"
                  :href="`#?idDimension=${customDimension.idcustomdimension}&scope=${scope.value}`"
                />
              </td>
            </tr>
          </tbody>
        </table>
        <div class="tableActionBar">
          <button
            class="btn"
            :disabled="!scope.numSlotsLeft"
            v-show="!isLoading"
            v-on:click="addDimension(scope.value)"
          >
            <span class="icon-add" /> {{ translate('CustomDimensions_ConfigureNewDimension') }}
            <span class="info">({{ translate(
              'CustomDimensions_XofYLeft',
              scope.numSlotsLeft,
              scope.numSlotsAvailable,
            ) }})</span>
          </button>
        </div>
      </ContentBlock>
    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import {
  translate,
  Matomo,
  MatomoUrl,
  ContentIntro,
  EnrichedHeadline,
  ContentBlock,
  ContentTable,
} from 'CoreHome';
import { ucfirst } from '../utilities';
import CustomDimensionsStore from '../CustomDimensions.store';
import { CustomDimension } from '../types';

export default defineComponent({
  name: 'listcustomdimensions',
  components: {
    EnrichedHeadline,
    ContentBlock,
  },
  directives: {
    ContentIntro,
    ContentTable,
  },
  created() {
    CustomDimensionsStore.fetch();
  },
  methods: {
    ucfirst(s: string) {
      return ucfirst(s);
    },
    addDimension(scope: string) {
      MatomoUrl.updateHashToUrl(`/?idDimension=0&scope=${scope}`);
    },
  },
  computed: {
    isLoading(): boolean {
      return CustomDimensionsStore.isLoading.value;
    },
    availableScopes(): (typeof CustomDimensionsStore)['availableScopes']['value'] {
      return CustomDimensionsStore.availableScopes.value;
    },
    contentIntroText(): string {
      const firstPart = translate(
        'CustomDimensions_CustomDimensionsIntroNext',
        '<a target=_blank href="https://piwik.org/docs/custom-variables">',
        '</a>',
        '<a target=_blank href="https://piwik.org/faq/general/faq_21117">',
        '</a>',
      );
      const secondPart = translate(
        'CustomDimensions_CustomDimensionsIntro',
        '<a target=_blank href="https://piwik.org/docs/custom-dimensions">',
        '</a>',
        this.siteName,
      );
      return `${firstPart}${secondPart}`;
    },
    customDimensions(): DeepReadonly<CustomDimension[]> {
      return CustomDimensionsStore.customDimensions.value;
    },
    sortedCustomDimensions(): DeepReadonly<CustomDimension>[] {
      const result = [...this.customDimensions];
      result.sort((lhs, rhs) => {
        const lhsId = parseInt(`${lhs.idcustomdimension}`, 10);
        const rhsId = parseInt(`${rhs.idcustomdimension}`, 10);
        return lhsId - rhsId;
      });
      return result;
    },
    sortedCustomDimensionsByScope(): Record<string, DeepReadonly<CustomDimension>[]> {
      const result: Record<string, DeepReadonly<CustomDimension>[]> = {};
      this.sortedCustomDimensions.reduce(
        (acc: typeof result, dim: DeepReadonly<CustomDimension>) => {
          acc[dim.scope] = acc[dim.scope] || [];
          acc[dim.scope].push(dim);
          return acc;
        },
        result,
      );
      return result;
    },
    siteName(): string {
      return Matomo.helper.htmlDecode(Matomo.siteName);
    },
  },
});
</script>
