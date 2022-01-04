<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="!siteIsBeingEdited" class="sitesButtonBar clearfix">

    <a v-show="hasSuperUserAccess && availableTypes"
       class="btn addSite"
       @click="addNewEntity()"
       tabindex="1">
      {{ availableTypes.length > 1
        ? translate('SitesManager_AddMeasurable')
        : translate('SitesManager_AddSite') }}
    </a>

    <div class="search" v-show="hasPrev || hasNext || isSearching">
      <input
        v-model="searchTerm"
        @keydown="searchSiteOnEnter($event)"
        :placeholder="translate('Actions_SubmenuSitesearch')"
        type="text"
      />
      <img
        @click="searchSite()"
        :title="translate('General_ClickToSearch')"
        class="search_ico"
        src="plugins/Morpheus/images/search_ico.png"
      />
    </div>

    <div class="paging" v-show="hasPrev || hasNext">
      <a
        class="btn prev"
        :disabled="hasPrev && !isLoading ? undefined : true"
        @click="previousPage()"
      >
        <span style="cursor:pointer;">&#171; {{ translate('General_Previous') }}</span>
      </a>
      <span class="counter" ng-show="adminSites.hasPrev || adminSites.hasNext">
            <span v-if="isSearching">
                {{ translate('General_PaginationWithoutTotal', offsetStart, offsetEnd) }}
            </span>
            <span v-if="!isSearching">
              {{ translate(
                'General_Pagination',
                offsetStart,
                offsetEnd,
                totalNumberOfSites === null ? '?' : totalNumberOfSites) }}
            </span>
        </span>
      <a class="btn next" :disabled="hasNext && !isLoading ? undefined : true" @click="nextPage()">
        <span style="cursor:pointer;" class="pointer">{{ translate('General_Next') }} &#187;</span>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo } from 'CoreHome';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';

interface AddSiteLinkState {
  searchTerm: string;
}
// TODO: rename ButtonBar
export default defineComponent({
  props: {
    siteIsBeingEdited: {
      type: Boolean,
      required: true,
    },
    hasPrev: {
      type: Boolean,
      required: true,
    },
    hasNext: {
      type: Boolean,
      required: true,
    },
    offsetStart: {
      type: Number,
      required: true,
    },
    offsetEnd: {
      type: Number,
      required: true,
    },
    totalNumberOfSites: {
      type: Number,
    },
    isSearching: {
      type: Boolean,
      required: true,
    },
    isLoading: {
      type: Boolean,
      required: true,
    },
  },
  data(): AddSiteLinkState {
    return {
      searchTerm: '',
    };
  },
  emits: ['add', 'search', 'prev', 'next'],
  computed: {
    hasSuperUserAccess() {
      return Matomo.hasSuperUserAccess;
    },
    availableTypes() {
      return SiteTypesStore.typesById.value;
    },
  },
  methods: {
    addNewEntity() {
      this.$emit('add');
    },
    searchSite() {
      this.$emit('search', this.searchTerm);
    },
    previousPage() {
      this.$emit('prev');
    },
    nextPage() {
      this.$emit('next');
    },
    searchSiteOnEnter(event: KeyboardEvent) {
      if (event.key !== 'Enter') {
        return;
      }

      event.preventDefault();

      this.searchSite();
    },
  },
});
</script>
