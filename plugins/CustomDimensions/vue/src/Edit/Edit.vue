<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- property types
- state types
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/CustomDimensions/angularjs/manage/edit.directive.js
  ./plugins/CustomDimensions/angularjs/manage/manage.directive.html
- create PR
</todo>

<template>
  <div class="editCustomDimension">
    <ContentBlock
      :content-title="translate('CustomDimensions_ConfigureDimension', ucfirst(dimensionScope), dimension.index || '')"
    >
      <p v-show="isLoading || isUpdating">
        <span class="loadingPiwik"><img src="plugins/Morpheus/images/loading-blue.gif" />
          {{ translate('General_LoadingData') }}</span>
      </p>
      <div v-show="!isLoading">
        <form @submit="edit ? updateCustomDimension() : createCustomDimension()">
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
            v-show="doesScopeSupportExtraction()"
          >
            <h3 class="col s12">{{ translate('CustomDimensions_ExtractValue') }}</h3>
            <div class="col s12 m6">
              <div
                :class="`${index}extraction `"
                v-for="(index, extraction) in dimension.extractions"
                :key="TODO"
              >
                <div class="row">
                  <div class="col s12 m6">
                    <div>
                      <Field
                        uicontrol="select"
                        :name="`${index}dimension`"
                        v-model="dimension.extractions.index.dimension"
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
                        v-model="dimension.extractions.index.pattern"
                        :full-width="true"
                        :title="dimension.extractions.index.dimension === 'urlparam' ? 'url query string parameter' : 'eg. /blog/(.*)/'"
                      >
                      </Field>
                    </div>
                  </div>
                  <div class="col s12">
                    <span
                      class="icon-plus"
                      v-show="dimension.extractions.index.pattern"
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
                      v-show="dimension.extractions[0].pattern"
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
            value="Update"
            v-show="edit"
            :disabled="isUpdating"
          />
          <input
            class="btn create"
            type="submit"
            value="Create"
            v-show="create"
            :disabled="isUpdating"
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
          <pre v-select-on-focus="{}"><code>_paq.push(['setCustomDimension', {{ dimension.idcustomdimension }},
              '{{ translate('CustomDimensions_ExampleValue') }}']);</code></pre>
          <p
            v-html="$sanitize(translate('CustomDimensions_HowToTrackManuallyViaJsDetails', '<a target=_blank href=\u0027https://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions\u0027>', '</a>'))">
          </p>
          <p>
            {{ translate('CustomDimensions_HowToTrackManuallyViaPhp') }}
          </p>
          <pre v-select-on-focus="{}"><code>$tracker-&gt;setCustomDimension('{{ dimension.idcustomdimension }}',
              '{{ translate('CustomDimensions_ExampleValue') }}');</code></pre>
          <p>
            {{ translate('CustomDimensions_HowToTrackManuallyViaHttp') }}
          </p>
          <pre v-select-on-focus="{}">
            <code>&amp;dimension{{ dimension.idcustomdimension }}={{ translate('CustomDimensions_ExampleValue') }}</code>
          </pre>
        </div>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  ContentBlock,
  SelectOnFocus,
  NotificationsStore,
  NotificationType,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import CustomDimensionsStore from '../CustomDimensions.store';
import { CustomDimension } from '../types';


interface EditState {
  dimension: CustomDimension;
  isUpdating: boolean;
}

const notificationId = 'customdimensions';

export default defineComponent({
  props: {
    dimensionId: Number,
    dimensionScope: String, // TODO,
  },
  components: {
    ContentBlock,
    Field,
  },
  directives: {
    SelectOnFocus,
  },
  data(): EditState {
    return {
      dimension: {} as unknown as CustomDimension,
      isUpdating: false,
    };
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
        if (this.edit) {
          this.dimension = CustomDimensionsStore.customDimensions.value[this.dimensionId];
          if (this.dimension && !this.dimension.extractions.length) {
            this.addExtraction();
          }
        } else if (this.create) {
          this.dimension = {
            idSite: Matomo.idSite,
            name: '',
            active: false,
            extractions: [],
            scope: this.dimensionScope,
            case_sensitive: true,
          };
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
      this.isUpdating = true;
      CustomDimensionsStore.createOrUpdateDimension(
        this.dimension,
        'CustomDimensions.configureNewCustomDimension',
      ).then(() => {
        this.showNotification(translate('CustomDimensions_DimensionCreated'), 'success');
        CustomDimensionsStore.reload();
        Matomo.helper.lazyScrollToContent();
      });
    },
    updateCustomDimension() {
      this.isUpdating = true;
      CustomDimensionsStore.createOrUpdateDimension(
        this.dimension,
        'CustomDimensions.configureExistingCustomDimension',
      ).then(() => {
        this.showNotification(translate('CustomDimensions_DimensionUpdated'), 'success');
        CustomDimensionsStore.reload();
        Matomo.helper.lazyScrollToContent();
      });
    },
  },
  computed: {
    isLoading() {
      return CustomDimensionsStore.isLoading.value;
    },
    isUpdating() {
      return CustomDimensionsStore.isUpdating.value || this.isUpdating;
    },
    create() {
      return this.dimensionId === 0; // TODO: dimensionId in adapter needs to be parsed
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

      const dimensionScope = this.availableScopes.find((scope) => scope.value === this.dimension.scope);
      return dimensionScope && scope.supportsExtractions;
    },
  },
});
</script>
