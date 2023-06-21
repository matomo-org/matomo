<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageCustomDimensions">
    <div v-if="!editMode">
      <div>
        <CustomDimensionsList />
      </div>
      <ContentBlock
        id="customDimensionsCreateMoreDimensions"
        :content-title="translate('CustomDimensions_IncreaseAvailableCustomDimensionsTitle')"
      >
        <p>
          {{ translate('CustomDimensions_IncreaseAvailableCustomDimensionsTakesLong') }}
          <br/><br/>{{ translate('CustomDimensions_HowToCreateCustomDimension') }}
          <br/><br/>
        </p>
        <div>
          <pre v-copy-to-clipboard="{}"><code v-text="addCustomDimCode"></code></pre>
        </div>
        <p>
          {{ translate('CustomDimensions_HowToManyCreateCustomDimensions') }}
          {{ translate('CustomDimensions_ExampleCreateCustomDimensions', 5) }}
        </p>
        <div>
          <pre v-copy-to-clipboard="{}"><code v-text="addMultipleCustomDimCode"></code></pre>
        </div>
      </ContentBlock>
    </div>
    <div v-if="editMode">
      <div>
        <CustomDimensionsEdit
          :dimension-id="dimensionId"
          :dimension-scope="dimensionScope"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { watch, defineComponent } from 'vue';
import {
  Matomo,
  ContentBlock,
  CopyToClipboard,
  MatomoUrl,
} from 'CoreHome';
import CustomDimensionsList from '../List/List';
import CustomDimensionsEdit from '../Edit/Edit';

interface ManageState {
  editMode: boolean;
  dimensionId: number|null;
  dimensionScope: string;
}

export default defineComponent({
  components: {
    CustomDimensionsList,
    ContentBlock,
    CustomDimensionsEdit,
  },
  directives: {
    CopyToClipboard,
  },
  data(): ManageState {
    return {
      editMode: false,
      dimensionId: null,
      dimensionScope: '',
    };
  },
  created() {
    watch(() => MatomoUrl.hashParsed.value, () => {
      this.initState();
    });

    this.initState();
  },
  methods: {
    getValidDimensionScope(scope: string) {
      if (['action', 'visit'].indexOf(scope) !== -1) {
        return scope!;
      }

      return '';
    },
    initState() {
      // as we're not using angular router we have to handle it manually here
      const idDimension = MatomoUrl.hashParsed.value.idDimension as string;

      if (idDimension) {
        const scope = this.getValidDimensionScope(MatomoUrl.hashParsed.value.scope as string);

        if (idDimension === '0') {
          const parameters = {
            isAllowed: true,
            scope,
          };

          Matomo.postEvent('CustomDimensions.initAddDimension', parameters);

          if (parameters && !parameters.isAllowed) {
            this.editMode = false;
            this.dimensionId = null;
            this.dimensionScope = '';
            return;
          }
        }

        this.editMode = true;
        this.dimensionId = parseInt(idDimension, 10);
        this.dimensionScope = scope;
      } else {
        this.editMode = false;
        this.dimensionId = null;
        this.dimensionScope = '';
      }

      Matomo.helper.lazyScrollToContent();
    },
  },
  computed: {
    addCustomDimCode() {
      return './console customdimensions:add-custom-dimension --scope=action\n'
        + './console customdimensions:add-custom-dimension --scope=visit';
    },
    addMultipleCustomDimCode() {
      return './console customdimensions:add-custom-dimension --scope=action --count=5';
    },
  },
});
</script>
