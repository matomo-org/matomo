<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="editCustomDimension">
    <ContentBlock
      :content-title="contentTitleText"
    >
      <p v-show="isLoading || isUpdating">
        <span class="loadingPiwik">
          <img src="plugins/Morpheus/images/loading-blue.gif" />
          {{ translate('General_LoadingData') }}
        </span>
      </p>
      <div v-show="!isLoading">
        <form @submit.prevent="edit ? updateCustomDimension() : createCustomDimension()">
          <div>
            <Field
              uicontrol="text"
              name="name"
              v-model="dimension.name"
              :maxlength="255"
              :required="true"
              :title="translate('General_Name')"
              :inline-help="translate('CustomDimensions_NameAllowedCharacters')"
            >
            </Field>
          </div>
          <div>
            <Field
              uicontrol="checkbox"
              name="active"
              v-model="dimension.active"
              :title="translate('CorePluginsAdmin_Active')"
              :inline-help="translate('CustomDimensions_CannotBeDeleted')"
            >
            </Field>
          </div>
          <div
            class="row form-group"
            v-show="doesScopeSupportExtraction"
          >
            <h3 class="col s12">{{ translate('CustomDimensions_ExtractValue') }}</h3>
            <div class="col s12 m6">
              <div
                v-for="(extraction, index) in dimension.extractions"
                :class="`${index}extraction `"
                :key="index"
              >
                <div class="row">
                  <div class="col s12 m6">
                    <div>
                      <Field
                        uicontrol="select"
                        :name="`${index}dimension`"
                        v-model="extraction.dimension"
                        :full-width="true"
                        :options="extractionDimensionsOptions"
                      >
                      </Field>
                    </div>
                  </div>
                  <div class="col s12 m6">
                    <div>
                      <Field
                        uicontrol="text"
                        :name="`${index}pattern`"
                        v-model="extraction.pattern"
                        :full-width="true"
                        :title="extraction.dimension === 'urlparam'
                          ? translate('CustomDimensions_UrlQueryStringParameter')
                          : 'eg. /blog/(.*)/'"
                      >
                      </Field>
                    </div>
                  </div>
                  <div class="col s12">
                    <span
                      class="icon-plus"
                      v-show="extraction.pattern"
                      @click="addExtraction()"
                    />
                    <span
                      class="icon-minus"
                      v-show="dimension.extractions.length > 1"
                      @click="removeExtraction(index)"
                    />
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col s12">
                  <div>
                    <Field
                      uicontrol="checkbox"
                      name="casesensitive"
                      v-show="dimension.extractions[0]?.pattern"
                      v-model="dimension.case_sensitive"
                      :title="translate('Goals_CaseSensitive')"
                    >
                    </Field>
                  </div>
                </div>
              </div>
            </div>
            <div class="col s12 m6 form-help">
              {{ translate('CustomDimensions_ExtractionsHelp') }}
            </div>
          </div>
          <input
            class="btn update"
            type="submit"
            :value="translate('General_Update')"
            v-show="edit"
            :disabled="isUpdating"
            style="margin-right:3.5px;"
          />
          <input
            class="btn create"
            type="submit"
            :value="translate('General_Create')"
            v-show="create"
            :disabled="isUpdating"
            style="margin-right:3.5px;"
          />
          <a
            class="btn cancel"
            type="button"
            href="#list"
          >{{ translate('General_Cancel') }}</a>
        </form>
        <div
          class="alert alert-info howToTrackInfo"
          v-show="edit"
        >
          <strong>{{ translate('CustomDimensions_HowToTrackManuallyTitle') }}</strong>
          <p>
            {{ translate('CustomDimensions_HowToTrackManuallyViaJs') }}
          </p>
          <div>
            <pre v-copy-to-clipboard="{}">
              <code v-html="$sanitize(manuallyTrackCodeViaJs(dimension))"></code>
            </pre>
          </div>
          <p v-html="$sanitize(howToTrackManuallyText)"/>
          <p>
            {{ translate('CustomDimensions_HowToTrackManuallyViaPhp') }}
          </p>
          <div>
            <pre v-copy-to-clipboard="{}">
              <code v-html="$sanitize(manuallyTrackCodeViaPhp(dimension))"></code>
            </pre>
          </div>
          <p>
            {{ translate('CustomDimensions_HowToTrackManuallyViaHttp') }}
          </p>
          <div>
            <pre v-copy-to-clipboard="{}">
              <code v-html="$sanitize(manuallyTrackCode)"></code>
            </pre>
          </div>
        </div>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  clone,
  translate,
  Matomo,
  ContentBlock,
  CopyToClipboard,
  NotificationsStore,
  NotificationType,
  MatomoUrl,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import CustomDimensionsStore from '../CustomDimensions.store';
import { CustomDimension } from '../types';
import { ucfirst } from '../utilities';

interface EditState {
  dimension: CustomDimension;
  isUpdatingDim: boolean;
}

const notificationId = 'customdimensions';

export default defineComponent({
  props: {
    dimensionId: Number,
    dimensionScope: {
      type: String,
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
  data(): EditState {
    return {
      dimension: { extractions: [] } as unknown as CustomDimension,
      isUpdatingDim: false,
    };
  },
  created() {
    this.init();
  },
  watch: {
    dimensionId() {
      this.init();
    },
  },
  methods: {
    removeAnyCustomDimensionNotification() {
      NotificationsStore.remove(notificationId);
    },
    showNotification(message: string, context: NotificationType['context']) {
      NotificationsStore.show({
        message,
        context,
        id: notificationId,
        type: 'transient',
      });
    },
    init() {
      if (this.dimensionId !== null) {
        this.removeAnyCustomDimensionNotification();
      }

      CustomDimensionsStore.fetch().then(() => {
        if (this.edit && this.dimensionId) {
          this.dimension = clone(
            CustomDimensionsStore.customDimensionsById.value[this.dimensionId],
          ) as unknown as CustomDimension;

          if (this.dimension && !this.dimension.extractions.length) {
            this.addExtraction();
          }
        } else if (this.create) {
          this.dimension = {
            idsite: Matomo.idSite,
            name: '',
            active: false,
            extractions: [],
            scope: this.dimensionScope,
            case_sensitive: true,
          } as unknown as CustomDimension;
          this.addExtraction();
        }
      });
    },
    removeExtraction(index: number) {
      if (index > -1) {
        this.dimension.extractions.splice(index, 1);
      }
    },
    addExtraction() {
      if (this.doesScopeSupportExtraction) {
        this.dimension.extractions.push({
          dimension: 'url',
          pattern: '',
        });
      }
    },
    createCustomDimension() {
      this.isUpdatingDim = true;
      CustomDimensionsStore.createOrUpdateDimension(
        this.dimension,
        'CustomDimensions.configureNewCustomDimension',
      ).then(() => {
        this.showNotification(translate('CustomDimensions_DimensionCreated'), 'success');
        CustomDimensionsStore.reload();
        MatomoUrl.updateHashToUrl('/list');
      }).finally(() => {
        this.isUpdatingDim = false;
      });
    },
    updateCustomDimension() {
      this.isUpdatingDim = true;
      CustomDimensionsStore.createOrUpdateDimension(
        this.dimension,
        'CustomDimensions.configureExistingCustomDimension',
      ).then(() => {
        this.showNotification(translate('CustomDimensions_DimensionUpdated'), 'success');
        CustomDimensionsStore.reload();
        MatomoUrl.updateHashToUrl('/list');
      }).finally(() => {
        this.isUpdatingDim = false;
      });
    },
    manuallyTrackCodeViaJs(dimension: CustomDimension) {
      return `_paq.push(['setCustomDimension', ${dimension.idcustomdimension}, `
        + `'${translate('CustomDimensions_ExampleValue')}']);`;
    },
    manuallyTrackCodeViaPhp(dimension: CustomDimension) {
      return `$tracker->setCustomDimension('${dimension.idcustomdimension}', `
        + `'${translate('CustomDimensions_ExampleValue')}');`;
    },
  },
  computed: {
    isLoading() {
      return CustomDimensionsStore.isLoading.value;
    },
    isUpdating() {
      return CustomDimensionsStore.isUpdating.value || this.isUpdatingDim;
    },
    create() {
      return this.dimensionId === 0;
    },
    edit() {
      return !this.create;
    },
    extractionDimensionsOptions() {
      return CustomDimensionsStore.extractionDimensionsOptions.value;
    },
    availableScopes() {
      return CustomDimensionsStore.availableScopes.value;
    },
    doesScopeSupportExtraction() {
      if (!this.dimension?.scope || !this.availableScopes) {
        return false;
      }

      const dimensionScope = this.availableScopes.find(
        (scope) => scope.value === this.dimension.scope,
      );
      return dimensionScope?.supportsExtractions;
    },
    contentTitleText() {
      return translate(
        'CustomDimensions_ConfigureDimension',
        ucfirst(this.dimensionScope),
        `${this.dimension?.index || ''}`,
      );
    },
    howToTrackManuallyText() {
      const link = 'https://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions';
      return translate(
        'CustomDimensions_HowToTrackManuallyViaJsDetails',
        `<a target=_blank href="${link}" rel="noreferrer noopener">`,
        '</a>',
      );
    },
    manuallyTrackCode() {
      const exampleValue = translate('CustomDimensions_ExampleValue');
      return `&dimension${this.dimension.idcustomdimension}=${exampleValue}`;
    },
  },
});
</script>
